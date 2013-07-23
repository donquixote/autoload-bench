<?php

namespace Seld\AutoloadBench;

class Generator
{
    protected $classes = [
        'FlattenExceptionTest', 'ClosureLoader', 'Hour2401Transformer',
        'WebProfilerBundle', 'MemcachedCache', 'ErrorHandlerTest',
        'ResolveInvalidReferencesPassTest', 'ListCommand', 'CustomNormalizerTest',
        'FixedFilterListener', 'functions', 'NativeProxy', 'FileCacheReaderTest',
        'EsiListener', 'ArgvInputTest', 'AddCacheWarmerPassTest',
        'NotifyPropertyChanged', 'MethodNotAllowedHttpException', 'PropertyPathTest',
        'ConfigurationTest', 'SecurityFactoryInterface', 'InteractiveLoginEvent',
        'ExecutableFinder', 'ValidationListener', 'TranslationDumperPass',
        'ResponseHeaderBag', 'NativeProxyTest', 'RealIteratorTestCase',
        'RedirectControllerTest', 'OutputFormatterInterface', 'UniqueEntity',
        'DateComparator', 'ContainerInterface', 'AbstractProfilerStorageTest',
        'IdentityTranslatorTest', 'SlotsHelperTest', 'CacheWarmerAggregateTest',
        'Serializer', 'TokenNotFoundException', 'FilesystemTest', 'EmailValidator',
        'DateTimeTestCase', 'ChromePhpHandler', 'CheckCircularReferencesPassTest',
        'FormEvents', 'BasicPermissionMapTest', 'StreamOutput', 'ChainLoader',
        'ProcessTest', 'CombinedSelectorNodeTest', 'Debugger', 'OptionsTest',
        'ProcessorTest', 'ConstraintTest', 'UserTest', 'MongoDbSessionHandler',
        'YearTransformer', 'DayTransformer', 'SessionInterface', 'PerformanceTest',
        'MaxLength', 'Package', 'validpattern', 'Foo3Command',
        'RetryAuthenticationEntryPoint', 'CollectionValidatorArrayTest', 'LocaleValidator',
        'ContainerAwareLoader', 'FileBagTest', 'ContainerAwareEventManagerTest',
        'ConstraintViolationList', 'Shell', 'PathPackage', 'ServerRunCommand',
        'ClassWithConstants', 'MessageSelector', 'RoleHierarchy',
        'SerializerAwareNormalizer', 'PropertyAccessDeniedException', 'DocParserTest',
        'UsernamePasswordToken', 'MergeTest', 'ObjectIdentityRetrievalStrategyTest',
        'MethodArgumentNotImplementedException', 'FormHelperDivLayoutTest',
        'AnonymousToken', 'UrlMatcherTest', 'AnonymousTokenTest',
        'MongoDbProfilerStorageTest', 'XPathExpr', 'PermissionGrantingStrategyInterface',
        'PHPDriverTest', 'TrueValidatorTest', 'FormExtensionTableLayoutTest',
    ];

    protected $namespaces = [
        'BrowserKit', 'Cms', 'Profiler', 'Extension', 'Authorization', 'FooBundle',
        'Event', 'Driver', 'Locale', 'Factory', 'RememberMe', 'EventListener',
        'DataCollector', 'Validator', 'Authentication', 'Pearlike', 'Dumper',
        'FrameworkBundle', 'Permission', 'Acl', 'Type', 'HttpFoundation', 'Mapping',
        'Generator', 'Normalizer', 'Flash', 'Namespaced', 'Doctrine', 'Http',
        'Test', 'Custom', 'CssSelector', 'SecurityBundle', 'Constraints',
        'Definition', 'CompilerPass', 'File', 'ExtensionAbsentBundle', 'Builder',
        'Templating', 'Form', 'Functional', 'ChoiceList', 'NamespaceCollision',
        'Controller', 'EventDispatcher', 'Attribute', 'Extractor', 'Handler',
        'FormTable', 'Exception', 'Fixtures', 'ExtensionPresentBundle',
        'BaseBundle', 'Asset', 'Swiftmailer', 'Debug', 'EntryPoint',
        'DataTransformer', 'Guess', 'Monolog', 'Collections', 'Propel1',
        'CacheWarmer', 'Twig', 'Field', 'StandardFormLogin', 'Encoder',
        'Annotations', 'CsrfProvider', 'Console', 'Logger', 'User',
        'WebProfilerBundle', 'Tester', 'Token', 'PrefixCollision', 'ParameterBag',
        'Config', 'Bridge', 'Logout', 'DateFormat', 'Core',
    ];

    public function generate(array $baseLevels, array $childLevels, $total)
    {
        return $this->generateRecursive(array_merge($baseLevels, $childLevels), $total, count($baseLevels) - 1);
    }

    protected function generateRecursive(array $levels, $total, $magicLevel) {
        if (empty($levels)) {
            $classes = $this->classes;
            $i = 0;
            while ($total > count($classes)) {
                foreach ($this->classes as $class) {
                    $classes[] = $class . ++$i;
                }
            }
            shuffle($classes);
            return array_fill_keys(array_slice($classes, 0, $total), TRUE);
        }
        $level = array_shift($levels);

        if (1 === $level) {
            $pieces = [$this->namespaces[array_rand($this->namespaces)]];
            $count = 1;
        }
        elseif (is_string($level)) {
            $pieces = [$level];
            $count = 1;
        }
        else {
            $pieces = $this->namespaces;
            $i = 0;
            while ($level > count($pieces)) {
                foreach ($this->namespaces as $piece) {
                    $pieces[] = $piece . ++$i;
                }
            }
            shuffle($pieces);
            $pieces = array_slice($pieces, 0, $level);
            $count = $level;
        }

        $classes = [];
        foreach ($pieces as $i => $piece) {
            if (0 === $magicLevel) {
                $classes[$piece] = [];
            }
            $partial = round($total / ($count - $i));
            $total -= $partial;
            foreach($this->generateRecursive($levels, $partial, $magicLevel - 1) as $class => $x) {
                if (0 === $magicLevel) {
                    $classes[$piece][$class] = $x;
                }
                else {
                    $classes[$piece . '\\' . $class] = $x;
                }
            }
        }
        if (0 === $magicLevel) {
            $classes = $this->shuffleAssoc($classes);
        }
        return $classes;
    }

    protected function shuffleAssoc(array $list)
    {
        $keys = array_keys($list);
        shuffle($keys);
        $random = [];
        foreach ($keys as $key) {
            $random[$key] = $list[$key];
        }
        return $random;
    }
}
