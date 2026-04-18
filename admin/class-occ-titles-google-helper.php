<?php
/**
 * Google Gemini Helper class file for OneClickContent Titles plugin.
 *
 * @package    Occ_Titles
 * @subpackage Occ_Titles/admin
 * @author     OneClickContent <support@oneclickcontent.com>
 */

defined( 'ABSPATH' ) || exit;

/**
 * The admin-specific functionality of the plugin concerning Google Gemini.
 *
 * Provides methods for interacting with the Google Gemini API.
 *
 * @link       https://oneclickcontent.com
 * @since      1.0.0
 *
 * @package    Occ_Titles
 * @subpackage Occ_Titles/admin
 */
class Occ_Titles_Google_Helper {

	/**
	 * Generate titles using the Google Gemini API (unchanged for now).
	 *
	 * @since  1.0.0
	 * @param  string $api_key The Google Gemini API key.
	 * @param  string $content The content to generate titles for.
	 * @param  string $style   Optional style for the titles.
	 * @param  string $request_id Optional request identifier.
	 * @param  int    $count   Number of titles to generate.
	 * @param  string $seed_title Optional seed title for refinement.
	 * @param  string $variation Optional refinement variation.
	 * @param  string $keyword Optional keyword for refinement.
	 * @param  array  $voice_profile Optional voice profile data.
	 * @param  array  $voice_samples Optional recent voice samples.
	 * @param  string $intent Optional generation intent.
	 * @param  array  $keywords Optional keyword targets.
	 * @param  int    $ellipsis Optional ellipsis toggle.
	 * @return array|string    Array of titles if successful, error message if failed.
	 */
	public function generate_titles_google( $api_key, $content, $style = '', $request_id = '', $count = 5, $seed_title = '', $variation = '', $keyword = '', $voice_profile = array(), $voice_samples = array(), $intent = '', $keywords = array(), $ellipsis = 0 ) {
		$model = get_option( 'occ_titles_google_model', 'gemini-2.5-flash' );

		if ( $count < 1 ) {
			$count = 1;
		} elseif ( $count > 5 ) {
			$count = 5;
		}

		Occ_Titles_Logger::get_instance()->info(
			'Google title generation started.',
			array(
				'request_id'     => $request_id,
				'model'          => $model,
				'content_length' => strlen( $content ),
				'style'          => $style,
				'count'          => $count,
				'seed_title'     => $seed_title,
				'variation'      => $variation,
				'intent'         => $intent,
				'keywords'       => $keywords,
			)
		);

		$system_instruction  = 'You are an SEO expert and content writer.';
		$system_instruction .= ' Your task is to generate exactly ' . $count . ' SEO-optimized title';
		$system_instruction .= 1 === (int) $count ? '' : 's';
		$system_instruction .= ' for the provided content. ';
		$system_instruction .= 'Each title should be engaging, include relevant keywords, and be between 50-60 characters long. ';
		$system_instruction .= 'Additionally, analyze the sentiment of each title (Positive, Negative, or Neutral).';

		if ( ! empty( $style ) ) {
			$system_instruction .= ' Use the following style for all titles: ' . ucfirst( $style ) . '.';
		} else {
			$system_instruction .= ' If no style is provided, choose the most suitable style from the following options: How-To, Listicle, Question, Command, ';
			$system_instruction .= 'Intriguing Statement, News Headline, Comparison, Benefit-Oriented, Storytelling, and Problem-Solution.';
		}

		if ( is_array( $voice_profile ) && ! empty( $voice_profile ) ) {
			$tone            = sanitize_text_field( $voice_profile['tone'] ?? '' );
			$formality       = sanitize_text_field( $voice_profile['formality'] ?? '' );
			$sentence_length = sanitize_text_field( $voice_profile['sentence_length'] ?? '' );
			$cta_style       = sanitize_text_field( $voice_profile['cta_style'] ?? '' );
			$must_use        = isset( $voice_profile['must_use'] ) ? array_map( 'sanitize_text_field', (array) $voice_profile['must_use'] ) : array();
			$avoid           = isset( $voice_profile['avoid'] ) ? array_map( 'sanitize_text_field', (array) $voice_profile['avoid'] ) : array();
			$examples        = isset( $voice_profile['examples'] ) ? array_map( 'sanitize_text_field', (array) $voice_profile['examples'] ) : array();

			if ( '' !== $tone ) {
				$system_instruction .= ' Tone: ' . $tone . '.';
			}
			if ( '' !== $formality ) {
				$system_instruction .= ' Formality: ' . $formality . '.';
			}
			if ( '' !== $sentence_length ) {
				$system_instruction .= ' Sentence length preference: ' . $sentence_length . '.';
			}
			if ( '' !== $cta_style ) {
				$system_instruction .= ' CTA style: ' . $cta_style . '.';
			}
			if ( ! empty( $must_use ) ) {
				$system_instruction .= ' Use these words where possible: ' . implode( ', ', $must_use ) . '.';
			}
			if ( ! empty( $avoid ) ) {
				$system_instruction .= ' Avoid these words: ' . implode( ', ', $avoid ) . '.';
			}

			$example_titles = array();
			if ( ! empty( $examples ) ) {
				$example_titles = array_merge( $example_titles, $examples );
			}
			if ( is_array( $voice_samples ) && ! empty( $voice_samples ) ) {
				$example_titles = array_merge( $example_titles, array_slice( $voice_samples, 0, 3 ) );
			}
			$example_titles = array_values( array_unique( array_filter( $example_titles ) ) );
			$example_titles = array_slice( $example_titles, 0, 5 );

			if ( ! empty( $example_titles ) ) {
				$system_instruction .= " Match the following example titles for voice consistency:\n- " . implode( "\n- ", $example_titles ) . "\n";
			}
		}

		if ( ! empty( $seed_title ) ) {
			$system_instruction .= ' Base the new title' . ( 1 === (int) $count ? '' : 's' ) . ' on this seed title: "' . $seed_title . '".';
		}

		if ( ! empty( $variation ) ) {
			$system_instruction .= ' Variation guidance: ' . ucfirst( $variation ) . '.';
		}

		if ( ! empty( $keyword ) ) {
			$system_instruction .= ' Include this keyword if it fits naturally: "' . $keyword . '".';
		}

		if ( ! empty( $intent ) ) {
			$system_instruction .= ' Primary goal: ' . $intent . '.';
		}

		$discover_guidance = $this->get_discover_guidance( $intent );
		if ( '' !== $discover_guidance ) {
			$system_instruction .= ' ' . $discover_guidance;
		}

		if ( $ellipsis ) {
			$system_instruction .= ' When it helps build curiosity, allow a few titles to end with an ellipsis. Do not end every title with an ellipsis.';
		}

		if ( ! empty( $keywords ) && is_array( $keywords ) ) {
			$system_instruction .= ' Target these keywords where possible: ' . implode( ', ', array_map( 'sanitize_text_field', $keywords ) ) . '.';
		}

		$system_instruction .= " Return the response as valid JSON in the following exact format:\n";
		$system_instruction .= "[\n";

		$format_lines = array();
		for ( $i = 1; $i <= $count; $i++ ) {
			$format_lines[] = "  { \"index\": {$i}, \"text\": \"Title {$i} content\", \"style\": \"Style\", \"sentiment\": \"Sentiment\", \"keywords\": [\"keyword1\", \"keyword2\"] }";
		}

		$system_instruction .= implode( ",\n", $format_lines );
		$system_instruction .= "\n]";

		$endpoint = self::get_generate_content_endpoint( $model );
		$schema   = self::get_title_response_schema();

		$body = wp_json_encode(
			array(
				'contents'         => array(
					array(
						'parts' => array(
							array(
								'text' => $system_instruction . "\n\nHere is the content:\n" . $content,
							),
						),
					),
				),
				'generationConfig' => array(
					'temperature'        => 0.7,
					'maxOutputTokens'    => 2000,
					'responseMimeType'   => 'application/json',
					'responseJsonSchema' => $schema,
				),
			)
		);

		$args = array(
			'headers' => self::get_request_headers( $api_key ),
			'body'    => $body,
			'method'  => 'POST',
			'timeout' => 120,
		);

		$response = wp_remote_post( $endpoint, $args );

		if ( is_wp_error( $response ) ) {
			$error_message = self::normalize_remote_error_message(
				$response->get_error_message(),
				__( 'Unable to connect to Google Gemini.', 'oneclickcontent-titles' )
			);
			Occ_Titles_Logger::get_instance()->error(
				'Google request failed.',
				array(
					'request_id' => $request_id,
					'error'      => $response->get_error_message(),
				)
			);
			return $error_message;
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );

		Occ_Titles_Logger::get_instance()->info(
			'Google response received.',
			array(
				'request_id'  => $request_id,
				'status'      => $response_code,
				'body_length' => strlen( $response_body ),
			)
		);

		$decoded = json_decode( $response_body, true );

		if ( 400 <= (int) $response_code ) {
			$error_message = self::normalize_remote_error_message(
				isset( $decoded['error']['message'] ) ? $decoded['error']['message'] : '',
				__( 'Google Gemini request failed.', 'oneclickcontent-titles' )
			);

			Occ_Titles_Logger::get_instance()->error(
				'Google request returned error response.',
				array(
					'request_id'    => $request_id,
					'response_code' => $response_code,
					'error_message' => $error_message,
				)
			);

			return $error_message;
		}

		$json_text = self::extract_candidate_text( $decoded );
		if ( '' === $json_text ) {
			Occ_Titles_Logger::get_instance()->error(
				'Google response missing expected content.',
				array(
					'request_id'    => $request_id,
					'response_code' => $response_code,
				)
			);
			return 'Unexpected response format.';
		}

		$titles = self::decode_title_payload( $json_text );
		if ( is_array( $titles ) ) {
			return $titles;
		}

		Occ_Titles_Logger::get_instance()->error(
			'Google response JSON decode failed.',
			array(
				'request_id'   => $request_id,
				'json_error'   => json_last_error_msg(),
				'text_preview' => substr( $json_text, 0, 500 ),
			)
		);

		return __( 'Google Gemini returned title data in an unexpected format. Try again or switch the Gemini model in settings.', 'oneclickcontent-titles' );
	}

	/**
	 * Build the response schema for title generation.
	 *
	 * @since 2.0.1
	 * @return array
	 */
	private static function get_title_response_schema() {
		return array(
			'type'  => 'array',
			'items' => array(
				'type'       => 'object',
				'properties' => array(
					'index'     => array(
						'type' => 'integer',
					),
					'text'      => array(
						'type' => 'string',
					),
					'style'     => array(
						'type' => 'string',
					),
					'sentiment' => array(
						'type' => 'string',
					),
					'keywords'  => array(
						'type'  => 'array',
						'items' => array(
							'type' => 'string',
						),
					),
				),
				'required'   => array( 'index', 'text', 'style', 'sentiment', 'keywords' ),
			),
		);
	}

	/**
	 * Build the Gemini generateContent endpoint.
	 *
	 * @since 2.0.1
	 * @param string $model Model slug.
	 * @return string
	 */
	private static function get_generate_content_endpoint( $model ) {
		return "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";
	}

	/**
	 * Build request headers for Gemini API calls.
	 *
	 * @since 2.0.1
	 * @param string $api_key API key.
	 * @return array
	 */
	private static function get_request_headers( $api_key ) {
		return array(
			'Content-Type'   => 'application/json',
			'x-goog-api-key' => $api_key,
		);
	}

	/**
	 * Fetch available Gemini text-generation models.
	 *
	 * @since 2.0.1
	 * @param string $api_key Google Gemini API key.
	 * @return array|false
	 */
	public static function get_available_google_models( $api_key ) {
		if ( empty( $api_key ) ) {
			return false;
		}

		$cache_key     = 'occ_titles_google_models_' . md5( $api_key );
		$cached_models = get_transient( $cache_key );
		if ( is_array( $cached_models ) && ! empty( $cached_models ) ) {
			return $cached_models;
		}

		$models     = array();
		$page_token = '';

		do {
			$endpoint = 'https://generativelanguage.googleapis.com/v1beta/models?pageSize=1000';
			if ( '' !== $page_token ) {
				$endpoint .= '&pageToken=' . rawurlencode( $page_token );
			}

			$response = wp_remote_get(
				$endpoint,
				array(
					'headers' => self::get_request_headers( $api_key ),
					'timeout' => 30,
				)
			);

			if ( is_wp_error( $response ) ) {
				Occ_Titles_Logger::get_instance()->warning(
					'Google models request failed.',
					array( 'error' => $response->get_error_message() )
				);
				return false;
			}

			$response_code = wp_remote_retrieve_response_code( $response );
			$response_body = wp_remote_retrieve_body( $response );
			$decoded       = json_decode( $response_body, true );

			if ( 400 <= (int) $response_code || ! isset( $decoded['models'] ) || ! is_array( $decoded['models'] ) ) {
				Occ_Titles_Logger::get_instance()->warning(
					'Google models request returned an unexpected response.',
					array(
						'response_code' => $response_code,
					)
				);
				return false;
			}

			foreach ( $decoded['models'] as $model_data ) {
				$model = self::normalize_google_model_option( $model_data );
				if ( ! empty( $model['value'] ) && ! empty( $model['label'] ) ) {
					$models[ $model['value'] ] = $model['label'];
				}
			}

			$page_token = isset( $decoded['nextPageToken'] ) ? trim( (string) $decoded['nextPageToken'] ) : '';
		} while ( '' !== $page_token );

		if ( empty( $models ) ) {
			return false;
		}

		uksort(
			$models,
			array( __CLASS__, 'compare_google_model_keys' )
		);

		$cache_ttl = defined( 'HOUR_IN_SECONDS' ) ? 6 * HOUR_IN_SECONDS : 21600;
		set_transient( $cache_key, $models, $cache_ttl );

		return $models;
	}

	/**
	 * Normalize a remote error message to safe plain text.
	 *
	 * @since 2.0.1
	 * @param string $message  Raw error message.
	 * @param string $fallback Fallback message.
	 * @return string
	 */
	private static function normalize_remote_error_message( $message, $fallback ) {
		$message = html_entity_decode( (string) $message, ENT_QUOTES, 'UTF-8' );
		$message = preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', ' ', $message );
		$message = wp_strip_all_tags( $message );
		$message = preg_replace( '/\s+/', ' ', $message );
		$message = trim( (string) $message );

		if ( '' === $message ) {
			return $fallback;
		}

		return $message;
	}

	/**
	 * Normalize a model response item into a select option.
	 *
	 * @since 2.0.1
	 * @param array $model_data Model payload item.
	 * @return array{value:string,label:string}
	 */
	private static function normalize_google_model_option( $model_data ) {
		$base_model = '';
		if ( isset( $model_data['baseModelId'] ) && is_string( $model_data['baseModelId'] ) ) {
			$base_model = trim( $model_data['baseModelId'] );
		} elseif ( isset( $model_data['name'] ) && is_string( $model_data['name'] ) ) {
			$base_model = trim( str_replace( 'models/', '', $model_data['name'] ) );
		}

		if ( '' === $base_model ) {
			return array(
				'value' => '',
				'label' => '',
			);
		}

		$model_key = strtolower( $base_model );
		if ( 0 !== strpos( $model_key, 'gemini' ) ) {
			return array(
				'value' => '',
				'label' => '',
			);
		}

		$supported_methods = isset( $model_data['supportedGenerationMethods'] ) && is_array( $model_data['supportedGenerationMethods'] )
			? $model_data['supportedGenerationMethods']
			: array();

		if ( ! in_array( 'generateContent', $supported_methods, true ) ) {
			return array(
				'value' => '',
				'label' => '',
			);
		}

		$excluded_fragments = array( 'image', 'tts', 'aqa', 'embedding', 'live', 'experimental', 'exp-' );
		foreach ( $excluded_fragments as $fragment ) {
			if ( false !== strpos( $model_key, $fragment ) ) {
				return array(
					'value' => '',
					'label' => '',
				);
			}
		}

		$label = isset( $model_data['displayName'] ) && is_string( $model_data['displayName'] ) && '' !== trim( $model_data['displayName'] )
			? trim( $model_data['displayName'] )
			: $base_model;

		return array(
			'value' => $base_model,
			'label' => $label,
		);
	}

	/**
	 * Sort preferred Gemini models to the top.
	 *
	 * @since 2.0.1
	 * @param string $left  Left model key.
	 * @param string $right Right model key.
	 * @return int
	 */
	private static function compare_google_model_keys( $left, $right ) {
		$preferred = array(
			'gemini-2.5-flash'      => 0,
			'gemini-2.5-flash-lite' => 1,
			'gemini-2.5-pro'        => 2,
		);

		$left_rank  = isset( $preferred[ $left ] ) ? $preferred[ $left ] : 100;
		$right_rank = isset( $preferred[ $right ] ) ? $preferred[ $right ] : 100;

		if ( $left_rank === $right_rank ) {
			return strnatcasecmp( $left, $right );
		}

		return $left_rank < $right_rank ? -1 : 1;
	}

	/**
	 * Extract candidate text from a Gemini API response.
	 *
	 * @since 2.0.1
	 * @param array $decoded Decoded response payload.
	 * @return string
	 */
	private static function extract_candidate_text( $decoded ) {
		if ( ! isset( $decoded['candidates'][0]['content']['parts'] ) || ! is_array( $decoded['candidates'][0]['content']['parts'] ) ) {
			return '';
		}

		$text_parts = array();
		foreach ( $decoded['candidates'][0]['content']['parts'] as $part ) {
			if ( isset( $part['text'] ) && is_string( $part['text'] ) && '' !== trim( $part['text'] ) ) {
				$text_parts[] = trim( $part['text'] );
			}
		}

		return trim( implode( "\n", $text_parts ) );
	}

	/**
	 * Decode a Gemini title payload into an array of titles.
	 *
	 * @since 2.0.1
	 * @param string $json_text Raw candidate text.
	 * @return array|null
	 */
	private static function decode_title_payload( $json_text ) {
		$payload = trim( (string) $json_text );

		if ( '' === $payload ) {
			return null;
		}

		if ( preg_match( '/```(?:json)?\s*(.*?)\s*```/is', $payload, $matches ) ) {
			$payload = trim( $matches[1] );
		}

		$decoded = json_decode( $payload, true );
		if ( json_last_error() === JSON_ERROR_NONE ) {
			return self::normalize_title_payload( $decoded );
		}

		if ( preg_match( '/(\[[\s\S]*\])/', $payload, $matches ) ) {
			$decoded = json_decode( $matches[1], true );
			if ( json_last_error() === JSON_ERROR_NONE ) {
				return self::normalize_title_payload( $decoded );
			}
		}

		if ( preg_match( '/(\{[\s\S]*\})/', $payload, $matches ) ) {
			$decoded = json_decode( $matches[1], true );
			if ( json_last_error() === JSON_ERROR_NONE ) {
				return self::normalize_title_payload( $decoded );
			}
		}

		return null;
	}

	/**
	 * Normalize decoded Gemini title payloads.
	 *
	 * @since 2.0.1
	 * @param mixed $decoded Decoded payload.
	 * @return array|null
	 */
	private static function normalize_title_payload( $decoded ) {
		if ( is_array( $decoded ) && isset( $decoded['titles'] ) && is_array( $decoded['titles'] ) ) {
			return array_values( $decoded['titles'] );
		}

		if ( is_array( $decoded ) && isset( $decoded[0] ) ) {
			return array_values( $decoded );
		}

		return null;
	}

	/**
	 * Provide Google Discover and Top Stories guidance when requested.
	 *
	 * @since 1.1.2
	 * @param string $intent Requested intent.
	 * @return string
	 */
	private function get_discover_guidance( $intent ) {
		$intent = strtolower( (string) $intent );
		if ( false === strpos( $intent, 'discover' ) && false === strpos( $intent, 'top stories' ) && false === strpos( $intent, 'top story' ) ) {
			return '';
		}

		return implode(
			' ',
			array(
				'For Google Discover and Top Stories cards, prioritize selection and engagement over strict keyword targeting.',
				'Lead with a recognizable entity, then add unresolved tension to create curiosity without being misleading.',
				'Keep the headline concise, timely, and high velocity in tone.',
				'Favor authority signaling and broad relevance, and avoid evergreen framing.',
				'Do not reference the image directly; the title must stand alone.',
			)
		);
	}

	/**
	 * Validates the Google Gemini API key with a simple "Hello" request.
	 *
	 * @since  1.0.0
	 * @param  string $api_key The API key to validate.
	 * @return bool            True if the key is valid (successful response), false otherwise.
	 */
	public static function validate_google_api_key( $api_key ) {
		$result = self::validate_google_api_key_request( $api_key );

		return ! empty( $result['success'] );
	}

	/**
	 * Validate the Google Gemini API key and return a structured result.
	 *
	 * @since 2.0.1
	 * @param string $api_key The API key to validate.
	 * @return array{success:bool,message:string}
	 */
	private static function validate_google_api_key_request( $api_key ) {
		if ( empty( $api_key ) ) {
			return array(
				'success' => false,
				'message' => __( 'Please enter a Google Gemini API key.', 'oneclickcontent-titles' ),
			);
		}

		$model    = get_option( 'occ_titles_google_model', 'gemini-2.5-flash' );
		$endpoint = self::get_generate_content_endpoint( $model );

		$body = wp_json_encode(
			array(
				'contents'         => array(
					array(
						'parts' => array(
							array(
								'text' => 'Say "Hello, Gemini!"',
							),
						),
					),
				),
				'generationConfig' => array(
					'maxOutputTokens' => 50,
				),
			)
		);

		$args = array(
			'headers' => self::get_request_headers( $api_key ),
			'body'    => $body,
			'method'  => 'POST',
			'timeout' => 30,
		);

		$response = wp_remote_post( $endpoint, $args );

		if ( is_wp_error( $response ) ) {
			return array(
				'success' => false,
				'message' => self::normalize_remote_error_message(
					$response->get_error_message(),
					__( 'Unable to connect to Google Gemini.', 'oneclickcontent-titles' )
				),
			);
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );

		$decoded = json_decode( $response_body, true );

		if ( 200 === $response_code && isset( $decoded['candidates'][0]['content']['parts'][0]['text'] ) ) {
			return array(
				'success' => true,
				'message' => __( 'API key is valid.', 'oneclickcontent-titles' ),
			);
		}

		if ( 400 <= (int) $response_code ) {
			return array(
				'success' => false,
				'message' => self::normalize_remote_error_message(
					isset( $decoded['error']['message'] ) ? $decoded['error']['message'] : '',
					__( 'Google Gemini request failed.', 'oneclickcontent-titles' )
				),
			);
		}

		return array(
			'success' => false,
			'message' => __( 'Unable to validate the Google Gemini API key.', 'oneclickcontent-titles' ),
		);
	}

	/**
	 * AJAX handler for validating the Google Gemini API key.
	 *
	 * @since 1.0.0
	 */
	public function occ_titles_ajax_validate_google_api_key() {
		check_ajax_referer( 'occ_titles_ajax_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Permission denied.', 'oneclickcontent-titles' ),
				)
			);
		}

		$api_key           = isset( $_POST['api_key'] ) ? sanitize_text_field( wp_unslash( $_POST['api_key'] ) ) : '';
		$validation_result = self::validate_google_api_key_request( $api_key );

		if ( ! empty( $validation_result['success'] ) ) {
			Occ_Titles_Settings::update_api_key_status( 'google', 'valid', __( 'API key is valid.', 'oneclickcontent-titles' ) );
			wp_send_json_success(
				array(
					'message' => $validation_result['message'],
				)
			);
		} else {
			Occ_Titles_Settings::update_api_key_status( 'google', 'invalid', $validation_result['message'] );
			wp_send_json_error(
				array(
					'message' => $validation_result['message'],
				)
			);
		}
	}
}
