<?php
include_once 'config/Database.php';

class XmlConverter
{

    private $conn;
    private $table_name = "inifini_datas";

    private $zip;

    public $file_path = '';

    public function __construct()
    {
        $database = new Database();
        $db = $database->getConnection();
        $this->conn = $db;
        $this->zip = new ZipArchive;
        $this->file_path = 'statics/data' . time() . '/';
    }

    public function convertXMLtoJsonPersistToDatabase($path)
    {

        try
        {

            if (!file_exists($path))
            {
                throw new Exception("Zip file not found");
            }

            if ($this
                ->zip
                ->open($path) === true)
            {

                if (!is_dir($this->file_path))
                {
                    mkdir($this->file_path, 0755, true);
                }
                $this
                    ->zip
                    ->extractTo($this->file_path);
                $this
                    ->zip
                    ->close();
                $extracted_path = $this->file_path . 'Object Definitions';
                $dir_array = $this->dirToArray($extracted_path);

                if (is_array($dir_array))
                {
                    $this->generateJSON($dir_array, $rest = false);
                }

            }
            else
            {

                throw new Exception("Failed to extract zip file.");
            }
        }
        catch(Exception $e)
        {
            throw new Exception($e->getMessage());
        }
        return true;

    }

    public function convertXMLtoJsonUploadViaAPI($path)
    {

        try
        {

            if (!file_exists($path))
            {
                throw new Exception("Zip file not found");
            }

            if ($this
                ->zip
                ->open($path) === true)
            {

                if (!is_dir($this->file_path))
                {
                    mkdir($this->file_path, 0755, true);
                }
                $this
                    ->zip
                    ->extractTo($this->file_path);
                $this
                    ->zip
                    ->close();
                $extracted_path = $this->file_path . 'Object Definitions';
                $dir_array = $this->dirToArray($extracted_path);

                if (is_array($dir_array))
                {
                    $d = $this->generateJSON($dir_array, $rest = true);
                    $jsonData = json_decode($d);
                    if (!empty($jsonData))
                    {
                        foreach ($jsonData as $key => $value)
                        {
                            $this->create(key($value) . '.xml', json_encode($value));

                        }
                    }

                }

            }
            else
            {

                throw new Exception("Failed to extract zip file.");
            }
        }
        catch(Exception $e)
        {
            throw new Exception($e->getMessage());
        }
        return true;

    }

    public function dirToArray($dir)
    {

        $result = array();

        $cdir = scandir($dir);
        foreach ($cdir as $key => $value)
        {
            if (!in_array($value, array(
                ".",
                ".."
            )))
            {
                if (is_dir($dir . DIRECTORY_SEPARATOR . $value))
                {
                    $result[$value] = $this->dirToArray($dir . DIRECTORY_SEPARATOR . $value);
                }
                else
                {
                    $result[] = $value;
                }
            }
        }

        return $result;
    }

    public function xmlToArray($xml, $options = array())
    {

        $defaults = array(
            'namespaceSeparator' => ':',
            'alwaysArray' => array() ,
            'autoArray' => true,
            'textContent' => '$',
            'autoText' => true,
            'keySearch' => false,
            'keyReplace' => false
        );

        $options = array_merge($defaults, $options);
        $namespaces = $xml->getDocNamespaces();
        $namespaces[''] = null;

        $attributesArray = array();
        foreach ($namespaces as $prefix => $namespace)
        {
            foreach ($xml->attributes($namespace) as $attributeName => $attribute)
            {
                if ($options['keySearch']) $attributeName = str_replace($options['keySearch'], $options['keyReplace'], $attributeName);
                $attributeKey = ($prefix ? $prefix . $options['namespaceSeparator'] : '') . $attributeName;
                $attributesArray[$attributeKey] = (string)$attribute;
            }
        }

        $tagsArray = array();
        foreach ($namespaces as $prefix => $namespace)
        {
            foreach ($xml->children($namespace) as $childXml)
            {

                $childArray = $this->xmlToArray($childXml, $options);
                list($childTagName, $childProperties) = $this->altEach($childArray);

                if ($options['keySearch']) $childTagName = str_replace($options['keySearch'], $options['keyReplace'], $childTagName);

                if ($prefix) $childTagName = $prefix . $options['namespaceSeparator'] . $childTagName;

                if (!isset($tagsArray[$childTagName]))
                {

                    $tagsArray[$childTagName] = in_array($childTagName, $options['alwaysArray']) || !$options['autoArray'] ? array(
                        $childProperties
                    ) : $childProperties;
                }
                elseif (is_array($tagsArray[$childTagName]) && array_keys($tagsArray[$childTagName]) === range(0, count($tagsArray[$childTagName]) - 1))
                {

                    $tagsArray[$childTagName][] = $childProperties;
                }
                else
                {

                    $tagsArray[$childTagName] = array(
                        $tagsArray[$childTagName],
                        $childProperties
                    );
                }
            }
        }

        $textContentArray = array();
        $plainText = trim((string)$xml);
        if ($plainText !== '') $textContentArray[$options['textContent']] = $plainText;

        $propertiesArray = !$options['autoText'] || $attributesArray || $tagsArray || ($plainText === '') ? array_merge($attributesArray, $tagsArray, $textContentArray) : $plainText;

        return array(
            $xml->getName() => $propertiesArray
        );
    }

    public function altEach($data)
    {

        $key = key($data);

        $ret = ($key === null) ? false : [$key, current($data) , 'key' => $key, 'value' => current($data) ];

        next($data);

        return $ret;

    }

    public function generateJSON($dir_array, $is_rest = false)
    {
        $datas_array = array();
        $extracted_path = $this->file_path . 'Object Definitions';
        foreach ($dir_array as $key => $value)
        {

            $categoryData = $this->xmlToArray(simplexml_load_file($extracted_path . '/' . $key . '/' . $value[0]));
            if (array_key_exists('Categories', $categoryData))
            {
                foreach ($categoryData['Categories']['Category'] as $cat)
                {
                    $final_array = array();
                    $cf = $extracted_path . '/' . $key . '/' . 'Custom Fields/' . $cat['treePosition'] . '.xml';
                    $customFields = $this->xmlToArray(simplexml_load_file($cf));

                    if (array_key_exists('CustomFields', $customFields['WFObjdCategory']))
                    {

                        foreach ($customFields['WFObjdCategory']['CustomFields']['CustomField'] as $cnt => $field)
                        {

                            $final_array[$cat['treePosition']][$cnt]['field_external_id'] = $cat['treePosition'] . '_CF_' . $cnt;
                            $final_array[$cat['treePosition']][$cnt]['field_object_code'] = $cat['treePosition'];
                            $final_array[$cat['treePosition']][$cnt]['field_object_entity_code'] = $cat['treePosition'];
                            $final_array[$cat['treePosition']][$cnt]['field_object_title'] = $cat['name'];
                            $final_array[$cat['treePosition']][$cnt]['field_object_category'] = $cat['treePosition'];
                            $final_array[$cat['treePosition']][$cnt]['field_object_category_name'] = $cat['name'];
                            $final_array[$cat['treePosition']][$cnt]['is_field_obj_cat_active'] = $cat['isActive'];
                            $final_array[$cat['treePosition']][$cnt]['uniqueKey'] = $field['name'];
                            $final_array[$cat['treePosition']][$cnt]['uniqueKeyFull'] = $cat['treePosition'] . '_' . $field['name'];
                            $final_array[$cat['treePosition']][$cnt]['detailFieldTypeIIDString'] = 'Memo Text';
                            $final_array[$cat['treePosition']][$cnt]['detailFieldTypeIID'] = $field['detailFieldTypeIID'];
                            $final_array[$cat['treePosition']][$cnt]['defaultValue'] = null;
                            $final_array[$cat['treePosition']][$cnt]['extraInfo'] = $field['extraInfo'];
                            $final_array[$cat['treePosition']][$cnt]['getLocalizedExtraInfo'] = $field['extraInfo'];
                            $final_array[$cat['treePosition']][$cnt]['name'] = $field['name'];
                            $final_array[$cat['treePosition']][$cnt]['objectQualifier'] = null;
                            $final_array[$cat['treePosition']][$cnt]['objectTitle'] = 'Detail Field';
                            $final_array[$cat['treePosition']][$cnt]['isDataMart'] = $field['isDataMart'];
                            $final_array[$cat['treePosition']][$cnt]['isExcludedFromCustomSearch'] = false;
                            $final_array[$cat['treePosition']][$cnt]['isExcludedFromGlobalSearch'] = false;;
                            $final_array[$cat['treePosition']][$cnt]['isRequired'] = $field['isRequired'];
                            $final_array[$cat['treePosition']][$cnt]['isTimeZoneIndependent'] = $field['isTimeZoneIndependent'];
                            $final_array[$cat['treePosition']][$cnt]['searchView'] = "";
                            $final_array[$cat['treePosition']][$cnt]['lookupTableCode'] = "";
                            $final_array[$cat['treePosition']][$cnt]['lookupTableName'] = "";
                            $final_array[$cat['treePosition']][$cnt]['field_counts_by_month'] = "";
                            $final_array[$cat['treePosition']][$cnt]['job_timestamp'] = "";

                        }
                    }
                    if (!empty($final_array))
                    {

                        if (!$is_rest)
                        {

                            $this->create(key($final_array) . '.xml', json_encode($final_array));

                        }
                        else
                        {

                            array_push($datas_array, $final_array);
                        }

                    }

                }

            }

        }

        if ($is_rest)
        {
            return json_encode($datas_array);
        }

    }

    public function create($data_file_name, $data_contents)
    {

        $this->deleteByFileName($data_file_name);

        $query = "INSERT INTO
                                " . $this->table_name . "
                            SET
                                data_file_name=:data_file_name, data_contents=:data_contents,created=:created";

        $stmt = $this
            ->conn
            ->prepare($query);

        $timestamp = date('Y-m-d H:i:s');

        $stmt->bindParam(":data_file_name", $data_file_name);
        $stmt->bindParam(":data_contents", $data_contents);
        $stmt->bindParam(":created", $timestamp);

        try
        {
            $stmt->execute();
            //echo "Success: Data persisted successfully for file: " . $data_file_name . "\r\n";
            
        }
        catch(Exception $e)
        {
            throw new Exception($e->getMessage());
        }
    }

    public function deleteByFileName($data_file_name)
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE data_file_name=:data_file_name";

        $stmt = $this
            ->conn
            ->prepare($query);
        $stmt->bindParam(":data_file_name", $data_file_name);

        try
        {
            $result = $stmt->execute();
        }
        catch(Exception $e)
        {
            throw new Exception($e->getMessage());
        }
    }

}
?>
