<?php declare(strict_types=1);

namespace  MoodleGQL;

use Closure;
use function count;
use Exception;
use function explode;
use   MoodleGQL\Type\CommentType;
use   MoodleGQL\Type\Enum\ContentFormatType;
use   MoodleGQL\Type\Enum\ImageSizeType;
use   MoodleGQL\Type\Enum\StoryAffordancesType;
use   MoodleGQL\Type\ImageType;
use   MoodleGQL\Type\NodeType;
use   MoodleGQL\Type\Scalar\EmailType;
use   MoodleGQL\Type\Scalar\UrlType;
use   MoodleGQL\Type\SearchResultType;
use   MoodleGQL\Type\StoryType;
use   MoodleGQL\Type\UserType;
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Type\Definition\Type;
use function lcfirst;
use function method_exists;
use function preg_replace;
use function strtolower;

/**
 * Acts as a registry and factory for your types.
 *
 * As simplistic as possible for the sake of clarity of this example.
 * Your own may be more dynamic (or even code-generated).
 */
final class Types
{
    /** @var array<string, Type> */
    public static array $types = [];

    public static function user(): callable
    {
        return self::get(UserType::class);
    }

    public static function story(): callable
    {
        return self::get(StoryType::class);
    }

    public static function comment(): callable
    {
        return self::get(CommentType::class);
    }

    public static function image(): callable
    {
        return self::get(ImageType::class);
    }

    public static function node(): callable
    {
        return self::get(NodeType::class);
    }

    public static function mention(): callable
    {
        return self::get(SearchResultType::class);
    }

    public static function imageSize(): callable
    {
        return self::get(ImageSizeType::class);
    }

    public static function contentFormat(): callable
    {
        return self::get(ContentFormatType::class);
    }

    public static function storyAffordances(): callable
    {
        return self::get(StoryAffordancesType::class);
    }

    public static function email(): callable
    {
        return self::get(EmailType::class);
    }

    public static function url(): callable
    {
        return self::get(UrlType::class);
    }

    /**
     * @param class-string<Type> $classname
     *
     * @return Closure(): Type
     */
    private static function get(string $classname): Closure
    {
        return static fn () => self::byClassName($classname);
    }

    /**
     * @param class-string<Type> $classname
     */
    public static function byClassName(string $classname): Type
    {
        $parts = explode('\\', $classname);

        $withoutTypePrefix = preg_replace('~Type$~', '', $parts[count($parts) - 1]);
        assert(is_string($withoutTypePrefix), 'regex is statically known to be correct');

        $cacheName = strtolower($withoutTypePrefix);

        if (! isset(self::$types[$cacheName])) {
//            echo "<PRE>";var_dump('PLN', self::$types[$cacheName] = new $classname());
            return self::$types[$cacheName] = new $classname();
        }

        return self::$types[$cacheName];
    }

    public static function byTypeName(string $shortName): Type
    {
        $cacheName = strtolower($shortName);
        $type = null;

        if (isset(self::$types[$cacheName])) {
            return self::$types[$cacheName];
        }

        $method = lcfirst($shortName);
        if (method_exists(self::class, $method)) {
            $type = self::{$method}();
        }

        if (! $type) {
            throw new Exception('Unknown graphql type: ' . $shortName);
        }

        return $type;
    }

    public static function boolean(): ScalarType
    {
        return Type::boolean();
    }

    public static function float(): ScalarType
    {
        return Type::float();
    }

    public static function id(): ScalarType
    {
        return Type::id();
    }

    public static function int(): ScalarType
    {
        return Type::int();
    }

    public static function string(): ScalarType
    {
        return Type::string();
    }
}
