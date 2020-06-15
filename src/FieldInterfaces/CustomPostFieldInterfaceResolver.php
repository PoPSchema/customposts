<?php

declare(strict_types=1);

namespace PoP\CustomPosts\FieldInterfaces;

use PoP\ComponentModel\Schema\SchemaDefinition;
use PoP\Translation\Facades\TranslationAPIFacade;
use PoP\ComponentModel\TypeResolvers\TypeResolverInterface;
use PoP\ComponentModel\FieldInterfaceResolvers\AbstractSchemaFieldInterfaceResolver;
use PoP\LooseContracts\Facades\NameResolverFacade;
use PoP\ComponentModel\FieldResolvers\EnumTypeSchemaDefinitionResolverTrait;
use PoP\CustomPosts\Types\Status;

class CustomPostFieldInterfaceResolver extends AbstractSchemaFieldInterfaceResolver
{
    use EnumTypeSchemaDefinitionResolverTrait;

    public const NAME = 'CustomPost';
    public const STATUSES = [
        Status::PUBLISHED,
        Status::PENDING,
        Status::DRAFT,
        Status::TRASH,
        'trashed',
    ];

    public function getInterfaceName(): string
    {
        return self::NAME;
    }

    public function getSchemaInterfaceDescription(): ?string
    {
        $translationAPI = TranslationAPIFacade::getInstance();
        return $translationAPI->__('Entities representing publishable \'content\', with a title', 'content');
    }

    public static function getFieldNamesToImplement(): array
    {
        return [
            'content',
            'url',
            'status',
            'isStatus',
            'date',
            'datetime',
            'title',
            'excerpt',
        ];
    }

    public function getSchemaFieldType(TypeResolverInterface $typeResolver, string $fieldName): ?string
    {
        $types = [
            'content' => SchemaDefinition::TYPE_STRING,
            'url' => SchemaDefinition::TYPE_URL,
            'status' => SchemaDefinition::TYPE_ENUM,
            'isStatus' => SchemaDefinition::TYPE_BOOL,
            'date' => SchemaDefinition::TYPE_DATE,
            'datetime' => SchemaDefinition::TYPE_DATE,
            'title' => SchemaDefinition::TYPE_STRING,
            'excerpt' => SchemaDefinition::TYPE_STRING,
        ];
        return $types[$fieldName] ?? parent::getSchemaFieldType($typeResolver, $fieldName);
    }

    public function isSchemaFieldResponseNonNullable(TypeResolverInterface $typeResolver, string $fieldName): bool
    {
        /**
         * Please notice that the URL, title and excerpt are nullable,
         * and content is not!
         */
        switch ($fieldName) {
            case 'content':
            case 'status':
            case 'isStatus':
            case 'date':
            case 'datetime':
                return true;
        }
        return parent::isSchemaFieldResponseNonNullable($typeResolver, $fieldName);
    }

    public function getSchemaFieldDescription(TypeResolverInterface $typeResolver, string $fieldName): ?string
    {
        $translationAPI = TranslationAPIFacade::getInstance();
        $descriptions = [
            'content' => $translationAPI->__('Custom post content', 'customposts'),
            'url' => $translationAPI->__('Custom post URL', 'customposts'),
            'postType' => $translationAPI->__('Custom post type', 'customposts'),
            'status' => $translationAPI->__('Custom post status', 'customposts'),
            'isStatus' => $translationAPI->__('Is the custom post in the given status?', 'customposts'),
            'date' => $translationAPI->__('Custom post published date', 'customposts'),
            'datetime' => $translationAPI->__('Custom post published date and time', 'customposts'),
            'title' => $translationAPI->__('Custom post title', 'customposts'),
            'excerpt' => $translationAPI->__('Custom post excerpt', 'customposts'),
        ];
        return $descriptions[$fieldName] ?? parent::getSchemaFieldDescription($typeResolver, $fieldName);
    }
    public function getSchemaFieldArgs(TypeResolverInterface $typeResolver, string $fieldName): array
    {
        $schemaFieldArgs = parent::getSchemaFieldArgs($typeResolver, $fieldName);
        $translationAPI = TranslationAPIFacade::getInstance();
        $cmsengineapi = \PoP\Engine\FunctionAPIFactory::getInstance();
        switch ($fieldName) {
            case 'date':
                return array_merge(
                    $schemaFieldArgs,
                    [
                        [
                            SchemaDefinition::ARGNAME_NAME => 'format',
                            SchemaDefinition::ARGNAME_TYPE => SchemaDefinition::TYPE_STRING,
                            SchemaDefinition::ARGNAME_DESCRIPTION => sprintf(
                                $translationAPI->__('Date format, as defined in %s', 'content'),
                                'https://www.php.net/manual/en/function.date.php'
                            ),
                            SchemaDefinition::ARGNAME_DEFAULT_VALUE => $cmsengineapi->getOption(NameResolverFacade::getInstance()->getName('popcms:option:dateFormat')),
                        ],
                    ]
                );

            case 'datetime':
                return array_merge(
                    $schemaFieldArgs,
                    [
                        [
                            SchemaDefinition::ARGNAME_NAME => 'format',
                            SchemaDefinition::ARGNAME_TYPE => SchemaDefinition::TYPE_STRING,
                            SchemaDefinition::ARGNAME_DESCRIPTION => sprintf(
                                $translationAPI->__('Date and time format, as defined in %s. Default value: \'%s\' (for current year date) or \'%s\' (otherwise)', 'content'),
                                'https://www.php.net/manual/en/function.date.php',
                                'j M, H:i',
                                'j M Y, H:i'
                            ),
                        ],
                    ]
                );

            case 'isStatus':
                return array_merge(
                    $schemaFieldArgs,
                    [
                        [
                            SchemaDefinition::ARGNAME_NAME => 'status',
                            SchemaDefinition::ARGNAME_TYPE => SchemaDefinition::TYPE_ENUM,
                            SchemaDefinition::ARGNAME_DESCRIPTION => $translationAPI->__('The status to check if the post has', 'content'),
                            SchemaDefinition::ARGNAME_ENUMVALUES => [
                                Status::PUBLISHED => [
                                    SchemaDefinition::ARGNAME_NAME => Status::PUBLISHED,
                                ],
                                Status::PENDING => [
                                    SchemaDefinition::ARGNAME_NAME => Status::PENDING,
                                ],
                                Status::DRAFT => [
                                    SchemaDefinition::ARGNAME_NAME => Status::DRAFT,
                                ],
                                Status::TRASH => [
                                    SchemaDefinition::ARGNAME_NAME => Status::TRASH,
                                ],
                                'trashed' => [
                                    SchemaDefinition::ARGNAME_NAME => 'trashed',
                                    SchemaDefinition::ARGNAME_DESCRIPTION => $translationAPI->__('Published content', 'content'),
                                    SchemaDefinition::ARGNAME_DEPRECATED => true,
                                    SchemaDefinition::ARGNAME_DEPRECATIONDESCRIPTION => sprintf(
                                        $translationAPI->__('Use \'%s\' instead', 'content'),
                                        Status::TRASH
                                    ),
                                ],
                            ],
                            SchemaDefinition::ARGNAME_MANDATORY => true,
                        ],
                    ]
                );
        }

        return $schemaFieldArgs;
    }

    protected function getSchemaDefinitionEnumValues(TypeResolverInterface $typeResolver, string $fieldName): ?array
    {
        switch ($fieldName) {
            case 'status':
                return self::STATUSES;
        }
        return null;
    }

    protected function getSchemaDefinitionEnumValueDeprecationDescriptions(TypeResolverInterface $typeResolver, string $fieldName): ?array
    {
        $translationAPI = TranslationAPIFacade::getInstance();
        switch ($fieldName) {
            case 'status':
                return [
                    'trashed' => sprintf(
                        $translationAPI->__('Using \'%s\' instead', 'content'),
                        Status::TRASH
                    ),
                ];
        }
        return null;
    }

    protected function getSchemaDefinitionEnumValueDescriptions(TypeResolverInterface $typeResolver, string $fieldName): ?array
    {
        $translationAPI = TranslationAPIFacade::getInstance();
        switch ($fieldName) {
            case 'status':
                return [
                    Status::PUBLISHED => $translationAPI->__('Published content', 'content'),
                    Status::PENDING => $translationAPI->__('Pending content', 'content'),
                    Status::DRAFT => $translationAPI->__('Draft content', 'content'),
                    Status::TRASH => $translationAPI->__('Trashed content', 'content'),
                    'trashed' => $translationAPI->__('Trashed content', 'content'),
                ];
        }
        return null;
    }
}