<?php

namespace   MoodleGQL\Type;

use MoodleGQL\TypeTransformer;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;

require_once(__DIR__ . '/../../../../../config.php');
require_once($CFG->dirroot.'/user/externallib.php');

// TODO move me somewhere else
$context = \context_system::instance();
$PAGE->set_context($context);

class QueryType extends ObjectType
{
    private static $count;

    public function getStructName() {
        return sprintf('struct_name_%s', self::$count++);
    }

    public function __construct()
    {
        $transformer = new TypeTransformer();
        $moodleOutput = \core_user_external::get_users_returns();
        $moodleInput = \core_user_external::get_users_parameters();
        $gqlOutput = $transformer
            ->setStructPrefix('get_users_returns')
            ->transformOutput($moodleOutput);
        $gqlInput = $transformer
            ->setStructPrefix('get_users_parameters')
            ->transformInput($moodleInput->keys['criteria']);

        parent::__construct([
            'name' => 'Query',
            'fields' => [
                'core_user_get_users' => [
                    'type' => $gqlOutput,
                    'args' => [
                        'criteria' => $gqlInput,
                    ],
                ],
            ],
            'resolveField' => function ($rootValue, $args, $context, ResolveInfo $info) {
                $users = \core_user_external::get_users($args['criteria']);
                return $users;
            },
        ]);
    }
}
