<?php
/**
 * OpenAI Helper class file for OneClickContent Titles plugin.
 *
 * @package    Occ_Titles
 * @subpackage Occ_Titles/admin
 * @author     OneClickContent <support@oneclickcontent.com>
 */

defined( 'ABSPATH' ) || exit;

/**
 * The admin-specific functionality of the plugin concerning OpenAI.
 *
 * Provides methods for interacting with the OpenAI API.
 *
 * @link       https://oneclickcontent.com
 * @since      1.0.0
 *
 * @package    Occ_Titles
 * @subpackage Occ_Titles/admin
 */
class Occ_Titles_OpenAI_Helper {

	/**
	 * Generate titles using the OpenAI API.
	 *
	 * @since  1.0.0
	 * @param  string $api_key The OpenAI API key.
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
	public function generate_titles_openai( $api_key, $content, $style = '', $request_id = '', $count = 5, $seed_title = '', $variation = '', $keyword = '', $voice_profile = array(), $voice_samples = array(), $intent = '', $keywords = array(), $ellipsis = 0 ) {
		$model = get_option( 'occ_titles_openai_model', 'gpt-4o-mini' );

		if ( $count < 1 ) {
			$count = 1;
		} elseif ( $count > 5 ) {
			$count = 5;
		}

		Occ_Titles_Logger::get_instance()->info(
			'OpenAI title generation started.',
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

		// Build the system instruction.
		$system_instruction  = 'You are an SEO expert and content writer.';
		$system_instruction .= ' Your task is to generate exactly ' . $count . ' SEO-optimized title';
		$system_instruction .= 1 === (int) $count ? '' : 's';
		$system_instruction .= ' for the provided content.';
		$system_instruction .= ' Each title should be engaging, include relevant keywords, and be between 50-60 characters long. ';
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

		// Use the Responses API for text generation.
		$endpoint = 'https://api.openai.com/v1/responses';

		$body = wp_json_encode(
			array(
				'model'        => $model,
				'instructions' => $system_instruction,
				'input'        => "Here is the content:\n" . $content,
				'temperature'  => 0.7,
			)
		);

		$args = array(
			'headers' => array(
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bearer ' . $api_key,
			),
			'body'    => $body,
			'timeout' => 120,
		);

		// Remove the logging of request args entirely.
		$args_log = $args;
		if ( isset( $args_log['headers']['Authorization'] ) ) {
			$args_log['headers']['Authorization'] = '[REDACTED]';
		}

		$response = wp_remote_post( $endpoint, $args );

		if ( is_wp_error( $response ) ) {
			$error_message = $this->normalize_remote_error_message(
				$response->get_error_message(),
				__( 'Unable to connect to OpenAI.', 'oneclickcontent-titles' )
			);
			Occ_Titles_Logger::get_instance()->error(
				'OpenAI request failed.',
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
			'OpenAI response received.',
			array(
				'request_id'  => $request_id,
				'status'      => $response_code,
				'body_length' => strlen( $response_body ),
			)
		);

		$decoded = json_decode( $response_body, true );

		if ( 400 <= (int) $response_code ) {
			$error_message = $this->normalize_remote_error_message(
				isset( $decoded['error']['message'] ) ? $decoded['error']['message'] : '',
				__( 'OpenAI request failed.', 'oneclickcontent-titles' )
			);
			if ( isset( $decoded['error']['message'] ) ) {
				$error_message = $this->normalize_remote_error_message(
					$decoded['error']['message'],
					$error_message
				);
			}

			Occ_Titles_Logger::get_instance()->error(
				'OpenAI request returned error response.',
				array(
					'request_id'    => $request_id,
					'response_code' => $response_code,
					'error_message' => $error_message,
				)
			);

			return $error_message;
		}

		$json_text = '';

		if ( isset( $decoded['output'] ) && is_array( $decoded['output'] ) ) {
			foreach ( $decoded['output'] as $output_item ) {
				if ( empty( $output_item['content'] ) || ! is_array( $output_item['content'] ) ) {
					continue;
				}
				foreach ( $output_item['content'] as $content_item ) {
					if ( isset( $content_item['type'], $content_item['text'] ) && 'output_text' === $content_item['type'] ) {
						$json_text .= $content_item['text'];
					}
				}
			}
		} elseif ( isset( $decoded['choices'][0]['message']['content'] ) ) {
			$json_text = (string) $decoded['choices'][0]['message']['content'];
		}

		if ( '' !== $json_text ) {
			$json_text = trim( $json_text );

			// Remove markdown code fences if present.
			if ( strpos( $json_text, '```' ) === 0 ) {
				$json_text = trim( preg_replace( '/^```(?:json)?(.*?)```$/s', '$1', $json_text ) );
			}

			$titles = json_decode( $json_text, true );
			if ( json_last_error() === JSON_ERROR_NONE ) {
				return $titles;
			} else {
				Occ_Titles_Logger::get_instance()->error(
					'OpenAI response JSON decode failed.',
					array(
						'request_id' => $request_id,
						'json_error' => json_last_error_msg(),
					)
				);
				return __( 'Unable to parse the response from OpenAI.', 'oneclickcontent-titles' );
			}
		} else {
			Occ_Titles_Logger::get_instance()->error(
				'OpenAI response missing expected content.',
				array(
					'request_id'    => $request_id,
					'response_code' => $response_code,
				)
			);
			return 'Unexpected response format.';
		}
	}

	/**
	 * Normalize a remote error message to safe plain text.
	 *
	 * @since 1.1.2
	 * @param string $message  Raw error message.
	 * @param string $fallback Fallback message.
	 * @return string
	 */
	private function normalize_remote_error_message( $message, $fallback ) {
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
	 * Validates the OpenAI API key and fetches models for completions.
	 *
	 * @since  1.0.0
	 * @param  string $api_key The API key to validate.
	 * @return array|bool      List of models if successful, false otherwise.
	 */
	public static function validate_openai_api_key( $api_key ) {
		if ( empty( $api_key ) ) {
			return false;
		}

		$response = wp_remote_get(
			'https://api.openai.com/v1/models',
			array(
				'headers' => array(
					'Content-Type'  => 'application/json',
					'Authorization' => 'Bearer ' . $api_key,
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			Occ_Titles_Logger::get_instance()->warning(
				'OpenAI API key validation request failed.',
				array( 'error' => $response->get_error_message() )
			);
			return false;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( isset( $data['data'] ) && is_array( $data['data'] ) ) {
			return array_map(
				function ( $model ) {
					return $model['id'];
				},
				$data['data']
			);
		}

		return false;
	}

	/**
	 * AJAX handler for validating the OpenAI API key.
	 *
	 * @since 1.0.0
	 */
	public function occ_titles_ajax_validate_openai_api_key() {
		check_ajax_referer( 'occ_titles_ajax_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Permission denied.', 'oneclickcontent-titles' ),
				)
			);
		}

		$api_key = isset( $_POST['api_key'] ) ? sanitize_text_field( wp_unslash( $_POST['api_key'] ) ) : '';
		$models  = self::validate_openai_api_key( $api_key );

		if ( $models ) {
			Occ_Titles_Settings::update_api_key_status( 'openai', 'valid', __( 'API key is valid.', 'oneclickcontent-titles' ) );
			wp_send_json_success(
				array(
					'message' => __( 'API key is valid.', 'oneclickcontent-titles' ),
					'models'  => $models,
				)
			);
		} else {
			Occ_Titles_Settings::update_api_key_status( 'openai', 'invalid', __( 'Invalid API key.', 'oneclickcontent-titles' ) );
			wp_send_json_error(
				array(
					'message' => __( 'Invalid API key.', 'oneclickcontent-titles' ),
				)
			);
		}
	}
}
