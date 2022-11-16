<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Manifest Generator.
 *
 * @package automattic/jetpack-autoloader
 */

// phpcs:disable WordPress.Files.FileName.InvalidClassFileName
// phpcs:disable WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
// phpcs:disable WordPress.NamingConventions.ValidVariableName.InterpolatedVariableNotSnakeCase
// phpcs:disable WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase
// phpcs:disable WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase
// phpcs:disable WordPress.PHP.DevelopmentFunctions.error_log_var_export

namespace Automattic\Jetpack\Autoloader;

/**
 * Class ManifestGenerator.
 */
class ManifestGenerator {

	/**
	 * Builds a manifest file for the given autoloader type.
	 *
	 * @param string $autoloaderType The type of autoloader to build a manifest for.
	 * @param string $fileName The filename of the manifest.
	 * @param array  $content The manifest content to generate using.
	 *
	 * @return string|null $manifestFile
	 * @throws \InvalidArgumentException When an invalid autoloader type is given.
	 */
	public static function buildManifest( $autoloaderType, $fileName, $content ) {
		if ( empty( $content ) ) {
			return null;
		}

		switch ( $autoloaderType ) {
			case 'classmap':
			case 'files':
				return self::buildStandardManifest( $fileName, $content );
			case 'psr-4':
				return self::buildPsr4Manifest( $fileName, $content );
		}

		throw new \InvalidArgumentException( 'An invalid manifest type of ' . $autoloaderType . ' was passed!' );
	}

	/**
	 * Builds the contents for the standard manifest file.
	 *
	 * @param string $fileName The filename we are building.
	 * @param array  $manifestData The formatted data for the manifest.
	 *
	 * @return string|null $manifestFile
	 */
	private static function buildStandardManifest( $fileName, $manifestData ) {
		$fileContent = PHP_EOL;
		foreach ( $manifestData as $key => $data ) {
			$key          = var_export( $key, true );
			$versionCode  = var_export( $data['version'], true );
			$fileContent .= <<<MANIFEST_CODE
	$key => array(
		'version' => $versionCode,
		'path'    => {$data['path']}
	),
MANIFEST_CODE;
			$fileContent .= PHP_EOL;
		}

		return self::buildFile( $fileName, $fileContent );
	}

	/**
	 * Builds the contents for the PSR-4 manifest file.
	 *
	 * @param string $fileName The filename we are building.
	 * @param array  $namespaces The formatted PSR-4 data for the manifest.
	 *
	 * @return string|null $manifestFile
	 */
	private static function buildPsr4Manifest( $fileName, $namespaces ) {
		$fileContent = PHP_EOL;
		foreach ( $namespaces as $namespace => $data ) {
			$namespaceCode = var_export( $namespace, true );
			$versionCode   = var_export( $data['version'], true );
			$pathCode      = 'array( ' . implode( ', ', $data['path'] ) . ' )';
			$fileContent  .= <<<MANIFEST_CODE
	$namespaceCode => array(
		'version' => $versionCode,
		'path'    => $pathCode
	),
MANIFEST_CODE;
			$fileContent  .= PHP_EOL;
		}

		return self::buildFile( $fileName, $fileContent );
	}

	/**
	 * Generate the PHP that will be used in the file.
	 *
	 * @param string $fileName The filename we are building.
	 * @param string $content The content to be written into the file.
	 *
	 * @return string $fileContent
	 */
	private static function buildFile( $fileName, $content ) {
		return <<<INCLUDE_FILE
<?php

// This file `$fileName` was auto generated by automattic/jetpack-autoloader.

\$vendorDir = dirname(__DIR__);
\$baseDir   = dirname(\$vendorDir);

return array($content);

INCLUDE_FILE;
	}
}
