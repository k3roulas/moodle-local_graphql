<?php declare(strict_types=1);
require_once __DIR__ . '/vendor/autoload.php';

use MoodleGQL\AppContext;
use MoodleGQL\Type\QueryType;
use MoodleGQL\Types;
use MoodleGQL\TypeTransformer;
use GraphQL\Server\StandardServer;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use GraphQL\Error\DebugFlag;

try {
    // See docs on schema options:
    // https://webonyx.github.io/graphql-php/type-system/schema/#configuration-options
    $schema = new Schema([
        'query' => new QueryType(),
        'typeLoader' => static function (string $name): Type {
            // search for the type into the static Types::$types array
            // and into TypeTransformer::$cache array
            try {
                return Types::byTypeName($name);
            } catch (Exception $e) {}
            return TypeTransformer::getType($name);
        }
    ]);

    // Prepare context that will be available in all field resolvers (as 3rd argument):
    $appContext = new AppContext();
    // TODO
    // Could add a user, for example :
    // $appContext->viewer = $currentlyLoggedInUser;
    $appContext->rootUrl = 'http://localhost';
    $appContext->request = $_REQUEST;

    // See docs on server options:
    // https://webonyx.github.io/graphql-php/executing-queries/#server-configuration-options
    $server = new StandardServer([
        'schema' => $schema,
        'context' => $appContext,
        'debugFlag' => DebugFlag::INCLUDE_DEBUG_MESSAGE | DebugFlag::INCLUDE_TRACE
    ]);

    $server->handleRequest();
} catch (Throwable $error) {
    StandardServer::send500Error($error);
}
