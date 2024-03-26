<?php

declare(strict_types=1);

use Rector\Caching\ValueObject\Storage\FileCacheStorage;
use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\CodeQuality\Rector\FuncCall\ArrayMergeOfNonArraysToSimpleArrayRector;
use Rector\CodeQuality\Rector\Ternary\ArrayKeyExistsTernaryThenValueToCoalescingRector;
use Rector\CodingStyle\Rector\Encapsed\EncapsedStringsToSprintfRector;
use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Php80\ValueObject\AnnotationToAttribute;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\Php80\Rector\Property\NestedAnnotationToAttributeRector;
use Rector\Php80\ValueObject\NestedAnnotationToAttribute;
use Rector\Symfony\Set\SymfonySetList;
use Rector\Symfony\Set\SensiolabsSetList;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\Php82\Rector\Class_\ReadOnlyClassRector;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromAssignsRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->cacheClass(FileCacheStorage::class);
    $rectorConfig->cacheDirectory('./var/cache/rector');
    $rectorConfig->importNames();
    $rectorConfig->paths([
        __DIR__ . '/src',
        __DIR__ . '/tests'
    ]);

    $rectorConfig->phpVersion(PhpVersion::PHP_82);
    $rectorConfig->rules(
        [
            InlineConstructorDefaultToPropertyRector::class,
            ArrayKeyExistsTernaryThenValueToCoalescingRector::class,
            ArrayMergeOfNonArraysToSimpleArrayRector::class,
            EncapsedStringsToSprintfRector::class,
        ]
    );
    $rectorConfig->skip(
        [
            TypedPropertyFromAssignsRector::class,
            ReadOnlyClassRector::class,
        ]
    );
    $rectorConfig->ruleWithConfiguration(
        AnnotationToAttributeRector::class,
        [
            new AnnotationToAttribute('Symfony\Component\Routing\Annotation\Route'),
            // new AnnotationToAttribute('Labstag\Annotation\IgnoreSoftDelete'),
            // new AnnotationToAttribute('Labstag\Annotation\Trashable'),
            // new AnnotationToAttribute('Labstag\Annotation\Uploadable'),
            // new AnnotationToAttribute('Labstag\Annotation\UploadableField'),
        ]
    );
    // $rectorConfig->ruleWithConfiguration(
    //     NestedAnnotationToAttributeRector::class,
    //     [
    //         new NestedAnnotationToAttribute(
    //             'Doctrine\ORM\Mapping\JoinTable',
    //             [
    //                 'joinColumns' => 'Doctrine\ORM\Mapping\JoinColumn',
    //                 'inverseJoinColumns' => 'Doctrine\ORM\Mapping\InverseJoinColumn',
    //             ]
    //         ),
    //     ]
    // );
    $rectorConfig->phpstanConfig(__DIR__ . '/phpstan.neon');
    // define sets of rules
    $rectorConfig->sets([
        DoctrineSetList::ANNOTATIONS_TO_ATTRIBUTES,
        SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES,
        SetList::CODE_QUALITY,
        SetList::CODING_STYLE,
        // SetList::DEAD_CODE,
        SetList::NAMING,
        SetList::PHP_82,
        // SetList::TYPE_DECLARATION,
        SensiolabsSetList::ANNOTATIONS_TO_ATTRIBUTES,
        LevelSetList::UP_TO_PHP_82
    ]);
};
