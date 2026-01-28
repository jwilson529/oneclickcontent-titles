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
	 * @return array|string    Array of titles if successful, error message if failed.
	 */
	public function generate_titles_google( $api_key, $content, $style = '', $request_id = '', $count = 5, $seed_title = '', $variation = '', $keyword = '' ) {
		$model = get_option( 'occ_titles_google_model', 'gemini-1.5-flash' ); // Default Gemini model.

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

		if ( ! empty( $seed_title ) ) {
			$system_instruction .= ' Base the new title' . ( 1 === (int) $count ? '' : 's' ) . ' on this seed title: "' . $seed_title . '".';
		}

		if ( ! empty( $variation ) ) {
			$system_instruction .= ' Variation guidance: ' . ucfirst( $variation ) . '.';
		}

		if ( ! empty( $keyword ) ) {
			$system_instruction .= ' Include this keyword if it fits naturally: "' . $keyword . '".';
		}

		$system_instruction .= " Return the response as valid JSON in the following exact format:\n";
		$system_instruction .= "[\n";

		$format_lines = array();
		for ( $i = 1; $i <= $count; $i++ ) {
			$format_lines[] = "  { \"index\": {$i}, \"text\": \"Title {$i} content\", \"style\": \"Style\", \"sentiment\": \"Sentiment\", \"keywords\": [\"keyword1\", \"keyword2\"] }";
		}

		$system_instruction .= implode( ",\n", $format_lines );
		$system_instruction .= "\n]";

		$endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$api_key}";

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
					'temperature'     => 0.7,
					'maxOutputTokens' => 2000,
				),
			)
		);

		$args = array(
			'headers' => array(
				'Content-Type' => 'application/json',
			),
			'body'    => $body,
			'method'  => 'POST',
			'timeout' => 120,
		);

		$response = wp_remote_post( $endpoint, $args );

		if ( is_wp_error( $response ) ) {
			$error_message = 'Request error: ' . $response->get_error_message();
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

		if ( isset( $decoded['candidates'][0]['content']['parts'][0]['text'] ) ) {
			$json_text = trim( $decoded['candidates'][0]['content']['parts'][0]['text'] );

			if ( strpos( $json_text, '```' ) === 0 ) {
				$json_text = trim( preg_replace( '/^```(?:json)?(.*?)```$/s', '$1', $json_text ) );
			}

			$titles = json_decode( $json_text, true );
			if ( json_last_error() === JSON_ERROR_NONE ) {
				return $titles;
			} else {
				$json_error = 'JSON decode error: ' . json_last_error_msg() . '. Raw response: ' . $json_text;
				Occ_Titles_Logger::get_instance()->error(
					'Google response JSON decode failed.',
					array(
						'request_id' => $request_id,
						'json_error' => json_last_error_msg(),
					)
				);
				return $json_error;
			}
		} else {
			Occ_Titles_Logger::get_instance()->error(
				'Google response missing expected content.',
				array(
					'request_id'    => $request_id,
					'response_code' => $response_code,
				)
			);
			return 'Unexpected response format.';
		}
	}

	/**
	 * Validates the Google Gemini API key with a simple "Hello" request.
	 *
	 * @since  1.0.0
	 * @param  string $api_key The API key to validate.
	 * @return bool            True if the key is valid (successful response), false otherwise.
	 */
	public static function validate_google_api_key( $api_key ) {
		if ( empty( $api_key ) ) {
			return false;
		}

		// Use a basic model for validation (e.g., gemini-1.5-flash).
		$model    = 'gemini-1.5-flash';
		$endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$api_key}";

		// Simple "Hello" prompt.
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
			'headers' => array(
				'Content-Type' => 'application/json',
			),
			'body'    => $body,
			'method'  => 'POST',
			'timeout' => 30,
		);

		$response = wp_remote_post( $endpoint, $args );

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );

		$decoded = json_decode( $response_body, true );

		// Check for a successful response (200 OK and content present).
		if ( 200 === $response_code && isset( $decoded['candidates'][0]['content']['parts'][0]['text'] ) ) {
			return true; // Key is valid if we get any response text.
		}

		return false;
	}

	/**
	 * AJAX handler for validating the Google Gemini API key.
	 *
	 * @since 1.0.0
	 */
	public function occ_titles_ajax_validate_google_api_key() {
		check_ajax_referer( 'occ_titles_ajax_nonce', 'nonce' );

		$api_key  = isset( $_POST['api_key'] ) ? sanitize_text_field( wp_unslash( $_POST['api_key'] ) ) : '';
		$is_valid = self::validate_google_api_key( $api_key );

		if ( $is_valid ) {
			Occ_Titles_Settings::update_api_key_status( 'google', 'valid', __( 'API key is valid.', 'oneclickcontent-titles' ) );
			wp_send_json_success(
				array(
					'message' => __( 'API key is valid.', 'oneclickcontent-titles' ),
				)
			);
		} else {
			Occ_Titles_Settings::update_api_key_status( 'google', 'invalid', __( 'Invalid API key.', 'oneclickcontent-titles' ) );
			wp_send_json_error(
				array(
					'message' => __( 'Invalid API key.', 'oneclickcontent-titles' ),
				)
			);
		}
	}
}
