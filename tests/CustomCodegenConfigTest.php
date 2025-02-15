<?hh // partial

namespace Slack\Hack\JsonSchema\Tests;

use type Slack\Hack\JsonSchema\Codegen\{IJsonSchemaCodegenConfig};

final class CustomCodegenConfigTest extends BaseCodegenTestCase {

  public function testCustomConfig(): void {
    $config = new CustomCodegenConfig();
    $ret = self::getBuilder(
      'person-schema.json',
      'CustomCodegenConfigValidator',
      shape(
        'sanitize_string' => shape(
          'uniline' => fun('\Slack\Hack\JsonSchema\Codegen\format'),
          'multiline' => fun('\Slack\Hack\JsonSchema\Codegen\format'),
        ),
        'json_schema_codegen_config' => $config,
      ),
    );
    $cf = $ret['codegen']->build();

    $rendered = $cf->render();
    $this->assertUnchanged($rendered);
  }

}

final class CustomCodegenConfig implements IJsonSchemaCodegenConfig {
  public function getTypeNamePrefix(): string {
    return '';
  }

  public function getTypeNameSuffix(): string {
    return '_t';
  }

  public function getClassNameFormatFunction(): (function(string...): string) {
    return fun('\Slack\Hack\JsonSchema\Codegen\format');
  }

  public function getTypeNameFormatFunction(): (function(string...): string) {
    return (string ...$parts) ==> {
      return \HH\Lib\Vec\map($parts, inst_meth($this, 'sanitize'))
        |> \HH\Lib\Str\join($$, '_');
    };
  }

  public function getFileNameFormatFunction(): (function(string...): string) {
    return fun('\Slack\Hack\JsonSchema\Codegen\format');
  }

  public function sanitize(string $input): string {
    return $input
      |> \str_replace('_', ' ', $$)
      |> \str_replace('-', ' ', $$)
      |> \str_replace('.', ' ', $$)
      |> \preg_split('/(?=[A-Z])/', $$)
      |> \array_filter($$)
      |> \HH\Lib\Vec\map($$, fun('\HH\Lib\Str\lowercase'))
      |> \HH\Lib\Str\join($$, ' ')
      |> \preg_replace("/[^A-Za-z0-9 ]/", ' nan ', $$)
      |> \str_replace(' ', '_', $$);
  }

}
