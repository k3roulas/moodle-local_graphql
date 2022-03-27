<?php


namespace  MoodleGQL;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class TypeTransformer
{
    static $count = 0;

    private string $structPrefix;
    static $cache = [];

    public static function getType($name) {
        if (isset(TypeTransformer::$cache[$name])) {
            return TypeTransformer::$cache[$name];
        }
        return null;
    }

    private function getStructName() : string {
        return sprintf('%s_%s', $this->structPrefix,  self::$count++);
    }

    public function setStructPrefix(string $prefix) : TypeTransformer {
        TypeTransformer::$count = 0;
        $this->structPrefix = $prefix;
        return $this;
    }

    private function getStructNameFromObject($object) : string {
        if ($object->desc) {
            return preg_replace('/[^a-zA-Z0-9_.]/', '_', $object->desc);
        } else {
            return $this->getStructName();
        }
    }

    private function getObjectType(string $name, $fields) : ObjectType {
        if (isset (TypeTransformer::$cache[$name])) {
            return TypeTransformer::$cache[$name];
        }
        $objectType = new ObjectType([
            'name' => $name,
            'fields' => $fields
        ]);

        TypeTransformer::$cache[$name] = $objectType;
        return $objectType;
    }

    private function getInputObjectType(string $name, $fields) : InputObjectType {
        if (isset (TypeTransformer::$cache[$name])) {
            return TypeTransformer::$cache[$name];
        }
        $objectType = new InputObjectType([
            'name' => $name,
            'fields' => $fields
        ]);

        TypeTransformer::$cache[$name] = $objectType;
        return $objectType;
    }

    public function transformOutput($input) {
        if (is_object($input)) {
            $class_name = get_class($input);
            switch ($class_name) {
                case 'external_multiple_structure':
                    /** @var external_multiple_structure $external_multiple_structure */
                    $external_multiple_structure = $input;
                    return new ListOfType($this->transformOutput($external_multiple_structure->content));
                    break;
                case 'external_single_structure':
                    /** @var external_single_structure $external_single_structure */
                    $external_single_structure = $input;
                    foreach ($external_single_structure->keys as $key => $attribute) {
                        if (!in_array($key, ['warnings', 'descriptionformat'])) {
                            $fields[$key] = $this->transformOutput($attribute);
                        }
                    }
                    $objectType = $this->getObjectType(
                        $this->getStructNameFromObject($external_single_structure),
                        $fields
                    );
                    return $objectType;
                    break;
                case 'external_value':
                    return [
                        'type' => Type::string()
                    ];

                    /** @var external_value $external_value */
                    $external_value = $input;
                    switch ($external_value->type) {
                        // TODO alpha and raw
                        case 'alpha':
                        case 'raw':
                            // TODO allownull, desc, required, default
                            return [
                                'type' => Type::string()
                            ];
                            break;
                    }
                    break;
            }
        }
    }

    public function transformInput($input) {
        if (is_object($input)) {
            $class_name = get_class($input);
            switch ($class_name) {
                case 'external_multiple_structure':
                    /** @var external_multiple_structure $external_multiple_structure */
                    $external_multiple_structure = $input;
                    return new ListOfType($this->transformInput($external_multiple_structure->content));
                    break;
                case 'external_single_structure':
                    /** @var external_single_structure $external_single_structure */
                    $external_single_structure = $input;
                    foreach ($external_single_structure->keys as $key => $attribute) {
                        if (!in_array($key, ['warnings', 'descriptionformat'])) {
                            $fields[$key] = $this->transformInput($attribute);
                        }
                    }
                    $objectType = $this->getInputObjectType(
                        $this->getStructNameFromObject($external_single_structure),
                        $fields
                    );
                    return $objectType;
                    break;
                case 'external_value':
                    $type = [];
                    /** @var external_value $external_value */
                    $external_value = $input;
                    switch ($external_value->type) {
                        case 'alpha':
                        case 'raw':
                            $type['type'] = Type::string();
                            break;
                    }
                    $allowNull = $external_value->allownull ?? false;
                    $desc = $external_value->desc ?? false;
                    $required = $external_value->required ?? false;
                    $default =  $external_value->required ?? false;
                    if ($required && !$allowNull) {
                        $type['type'] = Type::nonNull($type['type']);
                    }
                    if ($desc) {
                        $type['description'] = $desc;
                    }
                    if ($default) {
                        // TODO add a comment in the description
                    }
                    return $type;
                    break;
            }
        }
    }
}
