<?php
namespace WPCompatibility\Sniffs\Signature;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

class FunctionSniff implements Sniff {
	
	private static $data = null;
	

	
	public function __construct() {
		if(!$this::$data) {
			$this::$data = json_decode(file_get_contents( __DIR__ . '/data.json' ), true);
		}
		
	}
	
	private function is_wordpress_function($function_name) {
		return array_key_exists($function_name, $this::$data);
	}
	public $versions = null;
	private static function may_be_get_supported_versions() {
		$supported_versions = getenv('WP_COMPAT_PHPCS_SUPPORTED_VERSIONS');

		return is_string($supported_versions) ? $supported_versions : '3.7';
	}
	
	
	public function get_supported_versions() {
		if (!$this->versions) {
			$this->versions = self::may_be_get_supported_versions();
		}
		return explode(",", $this->versions);
	}
	
	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array<int|string>
	 */
	public function register() {
		return[
			T_STRING,
		];
		
	}

	public function process(File $phpcsFile, $stackPtr) {
		$tokens = $phpcsFile->getTokens();
		
		// Skip tokens that are the names of functions or classes
		// within their definitions. For example:
		// function myFunction...
		// "myFunction" is T_STRING but we should skip because it is not a
		// function or method *call*.
		$functionName    = $stackPtr;
		$ignoreTokens    = Tokens::$emptyTokens;
		$ignoreTokens[]  = T_BITWISE_AND;
		$functionKeyword = $phpcsFile->findPrevious($ignoreTokens, ($stackPtr - 1), null, true);
		if ($tokens[$functionKeyword]['code'] === T_FUNCTION || $tokens[$functionKeyword]['code'] === T_CLASS
		    || $tokens[$functionKeyword]['code'] === T_OBJECT_OPERATOR
		    || $tokens[$functionKeyword]['code'] === T_NULLSAFE_OBJECT_OPERATOR) {
			return;
		}
		
		if ($tokens[$stackPtr]['code'] === T_CLOSE_CURLY_BRACKET
		    && isset($tokens[$stackPtr]['scope_condition']) === true
		) {
			// Not a function call.
			return;
		}
		
		// If the next non-whitespace token after the function or method call
		// is not an opening parenthesis then it can't really be a *call*.
		$openBracket = $phpcsFile->findNext(Tokens::$emptyTokens, ($functionName + 1), null, true);
		if ($tokens[$openBracket]['code'] !== T_OPEN_PARENTHESIS) {
			return;
		}
		
		if (isset($tokens[$openBracket]['parenthesis_closer']) === false) {
			return;
		}

		$function_name = $tokens[$stackPtr]['content'];
		if (!$this->is_wordpress_function($function_name)) {
			return;
		}
		
		$supported_versions = $this->get_supported_versions();
		$function_data = self::$data[$function_name];
		foreach ($supported_versions as $supported_version) {
			
			$available_function = $this->get_available_function($supported_version, $function_data);
			if (!$available_function) {
				$phpcsFile->addError(
					sprintf('Function: %s is not available in wordpress version %s', $function_name, $supported_version),
					$stackPtr,
					'FunctionNotAvailable'
				);
				continue;
			}

			$args_data = $this->get_arguments_data($available_function['arguments']);
			$min_args = $args_data['min'];
			$max_args = $args_data['max'];
			$fn_call_args_count = $this->get_function_call_args_count($tokens, $phpcsFile, $stackPtr);

			// We don't care about passing more args to function since php don't throw any error.
			if ($fn_call_args_count < $min_args) {
				$phpcsFile->addError(
					sprintf('Function: %s signature did not match, required signature for wp version %s is `%s` expected atleast %d args but found only %d args',
						$function_name,
						$supported_version,
						"$function_name(" . $this->render_args($available_function['arguments']) . ")",
						$min_args,
						$fn_call_args_count
					),
					$stackPtr,
					'TooFewArguments'
				);
			}
		}
		
	}
	
	private function get_function_call_args_count($tokens, $phpcsFile, $stackPtr) {
		// Assuming that the next token after T_STRING is T_OPEN_PARENTHESIS.
		$open_parenthesis = $phpcsFile->findNext(T_OPEN_PARENTHESIS, $stackPtr + 1);
		$level = 0;
		$commas = 0;

		// as we discover open parenthesis, keep adding matching paren to stack
		for ($i = $open_parenthesis + 1; $i < count($tokens); $i++) {
			$token_data = $tokens[$i];
			$token_type = $token_data['code'];

			if ($token_type === T_OPEN_PARENTHESIS) {

				$level += 1;
			}
			elseif ($token_type === T_COMMA && 0 === $level) {
				
				$commas += 1;

			}
			elseif ($token_type === T_CLOSE_PARENTHESIS) {
				if($level === 0) {

					// function call ended, number of args would be number of commas plus 1
					return $commas + 1;
				}
				else {

					$level -= 1;
				}
			}
		}
		
		return $commas + 1;

	}
	
	private function ends_with($haystack, $needle) {
		$length = strlen( $needle );
		if( !$length ) {
			return true;
		}
		return substr( $haystack, -$length ) === $needle;
	}
	
	/**
	 * In our dataset, argument can either end with ::R or ::NR,
	 * ::R  => required, ::NR => not required
	 *
	 * @param $arguments_string_separated_by_comma
	 *
	 * @return array
	 */
	private function get_arguments_data($arguments_string_separated_by_comma) {
		$args = explode(',', $arguments_string_separated_by_comma);
		$required_args_count = 0;
		foreach ($args as $arg) {
			if ($this->ends_with($arg, '::R')) {
				$required_args_count += 1;
			}
		}
		return array(
			'max' => count($args), // At max we will have these arguments count,
			'min' => $required_args_count
		);
	}
	
	private function get_available_function($supported_version, $function_data) {
		$function_data = array_reverse($function_data);
		foreach ($function_data as $entry) {

			if (version_compare($entry['wp_version'], $supported_version, '<=')) {
				return $entry;
			}
		}
		return null;
	}
	
	/**
	 * @param $arguments
	 *
	 * @return string
	 */
	private function render_args( $arguments ) {
		$arguments = array_filter(explode(",", $arguments));
		$args = array_map(function ($item) {
			return '$' . rtrim(rtrim($item, '::R'), '::NR');
		}, $arguments);
		return join(", ", $args);
	}
}