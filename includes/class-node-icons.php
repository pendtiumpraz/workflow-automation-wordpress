<?php
/**
 * Node Icons Manager
 *
 * @link       https://opsguide.com
 * @since      1.0.0
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes
 */

/**
 * Node Icons class
 *
 * Manages icons for workflow nodes
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes
 * @author     OpsGuide Team <support@opsguide.com>
 */
class Node_Icons {

    /**
     * Get icon mappings
     *
     * @since    1.0.0
     * @return   array
     */
    public static function get_icon_mappings() {
        return array(
            // Start nodes
            'webhook_start' => array(
                'dashicon' => 'dashicons-admin-links',
                'svg' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M10.59 13.41c.41.39.41 1.03 0 1.42-.39.39-1.03.39-1.42 0a5.003 5.003 0 0 1 0-7.07l3.54-3.54a5.003 5.003 0 0 1 7.07 0 5.003 5.003 0 0 1 0 7.07l-1.49 1.49c.01-.82-.12-1.64-.4-2.42l.47-.48a2.982 2.982 0 0 0 0-4.24 2.982 2.982 0 0 0-4.24 0l-3.53 3.53a2.982 2.982 0 0 0 0 4.24m2.82-4.24c.39-.39 1.03-.39 1.42 0a5.003 5.003 0 0 1 0 7.07l-3.54 3.54a5.003 5.003 0 0 1-7.07 0 5.003 5.003 0 0 1 0-7.07l1.49-1.49c-.01.82.12 1.64.4 2.43l-.47.47a2.982 2.982 0 0 0 0 4.24 2.982 2.982 0 0 0 4.24 0l3.53-3.53a2.982 2.982 0 0 0 0-4.24.973.973 0 0 1 0-1.42Z"/></svg>',
                'color' => '#4CAF50'
            ),
            
            // Integration nodes
            'email' => array(
                'dashicon' => 'dashicons-email',
                'svg' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4-8 5-8-5V6l8 5 8-5v2z"/></svg>',
                'color' => '#2196F3'
            ),
            'slack' => array(
                'dashicon' => 'dashicons-format-status',
                'svg' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M5.042 15.165a2.528 2.528 0 0 1-2.52 2.523A2.528 2.528 0 0 1 0 15.165a2.527 2.527 0 0 1 2.522-2.52h2.52v2.52zM6.313 15.165a2.527 2.527 0 0 1 2.521-2.52 2.527 2.527 0 0 1 2.521 2.52v6.313A2.528 2.528 0 0 1 8.834 24a2.528 2.528 0 0 1-2.521-2.522v-6.313zM8.834 5.042a2.528 2.528 0 0 1-2.521-2.52A2.528 2.528 0 0 1 8.834 0a2.528 2.528 0 0 1 2.521 2.522v2.52H8.834zM8.834 6.313a2.528 2.528 0 0 1 2.521 2.521 2.528 2.528 0 0 1-2.521 2.521H2.522A2.528 2.528 0 0 1 0 8.834a2.528 2.528 0 0 1 2.522-2.521h6.312zM18.956 8.834a2.528 2.528 0 0 1 2.522-2.521A2.528 2.528 0 0 1 24 8.834a2.528 2.528 0 0 1-2.522 2.521h-2.522V8.834zM17.688 8.834a2.528 2.528 0 0 1-2.523 2.521 2.527 2.527 0 0 1-2.52-2.521V2.522A2.527 2.527 0 0 1 15.165 0a2.528 2.528 0 0 1 2.523 2.522v6.312zM15.165 18.956a2.528 2.528 0 0 1 2.523 2.522A2.528 2.528 0 0 1 15.165 24a2.527 2.527 0 0 1-2.52-2.522v-2.522h2.52zM15.165 17.688a2.527 2.527 0 0 1-2.52-2.523 2.526 2.526 0 0 1 2.52-2.52h6.313A2.527 2.527 0 0 1 24 15.165a2.528 2.528 0 0 1-2.522 2.523h-6.313z"/></svg>',
                'color' => '#4A154B'
            ),
            'http' => array(
                'dashicon' => 'dashicons-admin-site-alt3',
                'svg' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.94-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/></svg>',
                'color' => '#FF5722'
            ),
            'google_sheets' => array(
                'dashicon' => 'dashicons-media-spreadsheet',
                'svg' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/></svg>',
                'color' => '#0F9D58'
            ),
            'line' => array(
                'dashicon' => 'dashicons-format-chat',
                'svg' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M19.365 9.863c.349 0 .63.285.63.631 0 .345-.281.63-.63.63H17.61v1.125h1.755c.349 0 .63.283.63.63 0 .344-.281.629-.63.629h-2.386c-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.63-.63h2.386c.349 0 .63.285.63.63 0 .349-.281.63-.63.63H17.61v1.125h1.755zm-3.855 3.016c0 .27-.174.51-.432.596-.064.021-.133.031-.199.031-.211 0-.391-.09-.51-.25l-2.443-3.317v2.94c0 .344-.279.629-.631.629-.346 0-.626-.285-.626-.629V8.108c0-.27.173-.51.43-.595.06-.023.136-.033.194-.033.195 0 .375.104.495.254l2.462 3.33V8.108c0-.345.282-.63.63-.63.345 0 .63.285.63.63v4.771zm-5.741 0c0 .344-.282.629-.631.629-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.63-.63.346 0 .628.285.628.63v4.771zm-2.466.629H4.917c-.345 0-.63-.285-.63-.629V8.108c0-.345.285-.63.63-.63.349 0 .63.285.63.63v4.141h1.756c.348 0 .629.283.629.63 0 .344-.282.629-.629.629M24 10.314C24 4.943 18.615.572 12 .572S0 4.943 0 10.314c0 4.811 4.27 8.842 10.035 9.608.391.082.923.258 1.058.59.12.301.079.766.038 1.08l-.164 1.02c-.045.301-.24 1.186 1.049.645 1.291-.539 6.916-4.078 9.436-6.975C23.176 14.393 24 12.458 24 10.314"/></svg>',
                'color' => '#00C300'
            ),
            
            // AI nodes
            'openai' => array(
                'dashicon' => 'dashicons-admin-generic',
                'svg' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M22.282 9.821a5.985 5.985 0 0 0-.516-4.91 6.046 6.046 0 0 0-6.51-2.9A6.065 6.065 0 0 0 4.981 4.18a5.985 5.985 0 0 0-3.998 2.9 6.046 6.046 0 0 0 .743 7.097 5.975 5.975 0 0 0 .51 4.911 6.051 6.051 0 0 0 6.515 2.9A5.985 5.985 0 0 0 13.26 24a6.056 6.056 0 0 0 5.772-4.206 5.99 5.99 0 0 0 3.997-2.9 6.056 6.056 0 0 0-.747-7.073zM13.26 22.43a4.476 4.476 0 0 1-2.876-1.04l.141-.081 4.779-2.758a.795.795 0 0 0 .392-.681v-6.737l2.02 1.168a.071.071 0 0 1 .038.052v5.583a4.504 4.504 0 0 1-4.494 4.494zM3.6 18.304a4.47 4.47 0 0 1-.535-3.014l.142.085 4.783 2.759a.771.771 0 0 0 .78 0l5.843-3.369v2.332a.08.08 0 0 1-.033.062L9.74 19.95a4.5 4.5 0 0 1-6.14-1.646zM2.34 7.896a4.485 4.485 0 0 1 2.366-1.973V11.6a.766.766 0 0 0 .388.676l5.815 3.355-2.02 1.168a.076.076 0 0 1-.071 0l-4.83-2.786A4.504 4.504 0 0 1 2.34 7.872zm16.597 3.855l-5.833-3.387L15.119 7.2a.076.076 0 0 1 .071 0l4.83 2.791a4.494 4.494 0 0 1-.676 8.105v-5.678a.79.79 0 0 0-.407-.667zm2.01-3.023l-.141-.085-4.774-2.782a.776.776 0 0 0-.785 0L9.409 9.23V6.897a.066.066 0 0 1 .028-.061l4.83-2.787a4.5 4.5 0 0 1 6.68 4.66zm-12.64 4.135l-2.02-1.164a.08.08 0 0 1-.038-.057V6.075a4.5 4.5 0 0 1 7.375-3.453l-.142.08L8.704 5.46a.795.795 0 0 0-.393.681zm1.097-2.365l2.602-1.5 2.607 1.5v2.999l-2.597 1.5-2.607-1.5z"/></svg>',
                'color' => '#10A37F'
            ),
            'claude' => array(
                'dashicon' => 'dashicons-admin-generic',
                'svg' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z"/></svg>',
                'color' => '#7C3AED'
            ),
            'gemini' => array(
                'dashicon' => 'dashicons-star-filled',
                'svg' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>',
                'color' => '#4285F4'
            ),
            
            // Microsoft nodes
            'microsoft' => array(
                'dashicon' => 'dashicons-admin-site-alt3',
                'svg' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M0,0H11.377V11.372H0ZM12.623,0H24V11.372H12.623ZM0,12.623H11.377V24H0Zm12.623,0H24V24H12.623"/></svg>',
                'color' => '#00BCF2'
            ),
            
            // CRM nodes
            'hubspot' => array(
                'dashicon' => 'dashicons-groups',
                'svg' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M18.164 7.93V5.084a2.198 2.198 0 0 0 1.267-1.978v-.067A2.2 2.2 0 0 0 17.238.845h-.067a2.2 2.2 0 0 0-2.193 2.193v.067a2.196 2.196 0 0 0 1.252 1.973l.013.006v2.852a6.22 6.22 0 0 0-2.969 1.31l.012-.01-7.828-6.095A2.497 2.497 0 0 0 5.36.569h-.08a2.5 2.5 0 1 0 2.51 2.508 2.5 2.5 0 0 0-.233-.978l.005.013 7.843 6.107a6.25 6.25 0 0 0-1.47 3.651H9.522a2.2 2.2 0 0 0-1.978-1.267h-.062a2.2 2.2 0 0 0-2.199 2.193v.067a2.2 2.2 0 0 0 2.193 2.193h.068a2.203 2.203 0 0 0 1.975-1.265l-.005.012h3.428a6.222 6.222 0 0 0 3.041 3.228l-.017-.008v4.011a2.2 2.2 0 0 0-1.266 1.978v.067a2.2 2.2 0 0 0 2.193 2.193h.067a2.2 2.2 0 0 0 2.194-2.193V20.8a2.206 2.206 0 0 0-1.252-1.976l-.012-.005V14.81a6.216 6.216 0 0 0 2.96-6.671l.002.019a6.22 6.22 0 0 0-.693-2.24l.015.034zm-.923 6.223A3.39 3.39 0 0 1 13.883 17.3a3.396 3.396 0 0 1-3.413-3.38v-.033a3.39 3.39 0 0 1 3.157-3.379h.19c.131 0 .259.007.386.022l-.018-.002a3.39 3.39 0 0 1 3.055 3.365v.053a2.5 2.5 0 0 1 0 .205z"/></svg>',
                'color' => '#FF7A59'
            ),
            
            // Note-taking nodes
            'notion' => array(
                'dashicon' => 'dashicons-editor-table',
                'svg' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M4.459 4.208c.746.606 1.026.56 2.428.466l13.215-.793c.28 0 .047-.28-.046-.326L17.86 1.968c-.42-.326-.981-.7-2.055-.607L3.01 2.295c-.466.046-.56.28-.374.466zm.793 3.08v13.904c0 .747.373 1.027 1.214.98l14.523-.84c.841-.046.935-.56.935-1.167V6.354c0-.606-.233-.933-.748-.887l-15.177.887c-.56.047-.747.327-.747.933zm14.337.745c.093.42 0 .84-.42.888l-.7.14v10.264c-.608.327-1.168.514-1.635.514-.748 0-.935-.234-1.495-.933l-4.577-7.186v6.952L12.21 19s0 .84-1.168.84l-3.222.186c-.093-.186 0-.653.327-.746l.84-.233V9.854L7.822 9.76c-.094-.42.14-1.026.793-1.073l3.456-.233 4.764 7.279v-6.44l-1.215-.139c-.093-.514.28-.887.747-.933zM1.936 1.035l13.31-.98c1.634-.14 2.055-.047 3.082.7l4.249 2.986c.7.513.934.653.934 1.213v16.378c0 1.026-.373 1.634-1.68 1.726l-15.458.934c-.98.047-1.448-.093-1.962-.747l-3.129-4.06c-.56-.747-.793-1.306-.793-1.96V2.667c0-.839.374-1.54 1.447-1.632z"/></svg>',
                'color' => '#000000'
            ),
            
            // Messaging nodes
            'telegram' => array(
                'dashicon' => 'dashicons-format-status',
                'svg' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>',
                'color' => '#0088CC'
            ),
            'whatsapp' => array(
                'dashicon' => 'dashicons-format-chat',
                'svg' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>',
                'color' => '#25D366'
            ),
            
            // Logic nodes
            'filter' => array(
                'dashicon' => 'dashicons-filter',
                'svg' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M10 18h4v-2h-4v2zM3 6v2h18V6H3zm3 7h12v-2H6v2z"/></svg>',
                'color' => '#9C27B0'
            ),
            'loop' => array(
                'dashicon' => 'dashicons-controls-repeat',
                'svg' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 4V1L8 5l4 4V6c3.31 0 6 2.69 6 6 0 1.01-.25 1.97-.7 2.8l1.46 1.46A7.93 7.93 0 0 0 20 12c0-4.42-3.58-8-8-8zm0 14c-3.31 0-6-2.69-6-6 0-1.01.25-1.97.7-2.8L5.24 7.74A7.93 7.93 0 0 0 4 12c0 4.42 3.58 8 8 8v3l4-4-4-4v3z"/></svg>',
                'color' => '#607D8B'
            ),
            
            // Data nodes
            'transform' => array(
                'dashicon' => 'dashicons-randomize',
                'svg' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M10.59 9.17L5.41 4 4 5.41l5.17 5.17 1.42-1.41zM14.5 4l2.04 2.04L4 18.59 5.41 20 17.96 7.46 20 9.5V4h-5.5zm.33 9.41l-1.41 1.41 3.13 3.13L14.5 20H20v-5.5l-2.04 2.04-3.13-3.13z"/></svg>',
                'color' => '#795548'
            ),
            'formatter' => array(
                'dashicon' => 'dashicons-editor-code',
                'svg' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M9.4 16.6L4.8 12l4.6-4.6L8 6l-6 6 6 6 1.4-1.4zm5.2 0l4.6-4.6-4.6-4.6L16 6l6 6-6 6-1.4-1.4z"/></svg>',
                'color' => '#607D8B'
            ),
            'parser' => array(
                'dashicon' => 'dashicons-media-code',
                'svg' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>',
                'color' => '#FF9800'
            ),
            
            // WordPress nodes
            'wp_post' => array(
                'dashicon' => 'dashicons-admin-post',
                'svg' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M3 3v18h18V3H3zm16 16H5V5h14v14zM7 7h10v2H7V7zm0 4h10v2H7v-2zm0 4h7v2H7v-2z"/></svg>',
                'color' => '#21759B'
            ),
            'wp_user' => array(
                'dashicon' => 'dashicons-admin-users',
                'svg' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>',
                'color' => '#21759B'
            ),
            'wp_media' => array(
                'dashicon' => 'dashicons-admin-media',
                'svg' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/></svg>',
                'color' => '#21759B'
            ),
            
            // Default
            'default' => array(
                'dashicon' => 'dashicons-admin-generic',
                'svg' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>',
                'color' => '#757575'
            )
        );
    }

    /**
     * Get icon for node type
     *
     * @since    1.0.0
     * @param    string    $node_type    The node type
     * @param    string    $format       Icon format (dashicon, svg, color)
     * @return   string
     */
    public static function get_icon($node_type, $format = 'dashicon') {
        $icons = self::get_icon_mappings();
        
        if (!isset($icons[$node_type])) {
            $node_type = 'default';
        }
        
        if (!isset($icons[$node_type][$format])) {
            return '';
        }
        
        return $icons[$node_type][$format];
    }

    /**
     * Get all available icons
     *
     * @since    1.0.0
     * @return   array
     */
    public static function get_all_icons() {
        $icons = self::get_icon_mappings();
        $result = array();
        
        foreach ($icons as $type => $icon_data) {
            $result[$type] = array(
                'label' => ucwords(str_replace('_', ' ', $type)),
                'dashicon' => $icon_data['dashicon'],
                'color' => $icon_data['color']
            );
        }
        
        return $result;
    }

    /**
     * Render icon HTML
     *
     * @since    1.0.0
     * @param    string    $node_type    The node type
     * @param    array     $args         Additional arguments
     * @return   string
     */
    public static function render_icon($node_type, $args = array()) {
        $defaults = array(
            'size' => 24,
            'class' => '',
            'format' => 'svg' // svg or dashicon
        );
        
        $args = wp_parse_args($args, $defaults);
        
        if ($args['format'] === 'dashicon') {
            $dashicon = self::get_icon($node_type, 'dashicon');
            $color = self::get_icon($node_type, 'color');
            
            return sprintf(
                '<span class="dashicons %s %s" style="color: %s; font-size: %dpx; width: %dpx; height: %dpx;"></span>',
                esc_attr($dashicon),
                esc_attr($args['class']),
                esc_attr($color),
                intval($args['size']),
                intval($args['size']),
                intval($args['size'])
            );
        } else {
            $svg = self::get_icon($node_type, 'svg');
            $color = self::get_icon($node_type, 'color');
            
            // Replace currentColor with actual color
            $svg = str_replace('currentColor', $color, $svg);
            
            // Add size and class to SVG
            $svg = str_replace(
                '<svg',
                sprintf(
                    '<svg width="%d" height="%d" class="wa-node-icon %s"',
                    intval($args['size']),
                    intval($args['size']),
                    esc_attr($args['class'])
                ),
                $svg
            );
            
            return $svg;
        }
    }

    /**
     * Get node categories with icons
     *
     * @since    1.0.0
     * @return   array
     */
    public static function get_categories() {
        return array(
            'triggers' => array(
                'label' => __('Triggers', 'workflow-automation'),
                'icon' => 'dashicons-admin-links',
                'color' => '#4CAF50',
                'description' => __('Start your workflow with these triggers', 'workflow-automation')
            ),
            'actions' => array(
                'label' => __('Actions', 'workflow-automation'),
                'icon' => 'dashicons-admin-generic',
                'color' => '#2196F3',
                'description' => __('Perform actions in your workflow', 'workflow-automation')
            ),
            'logic' => array(
                'label' => __('Logic', 'workflow-automation'),
                'icon' => 'dashicons-randomize',
                'color' => '#9C27B0',
                'description' => __('Control flow and data in your workflow', 'workflow-automation')
            ),
            'data' => array(
                'label' => __('Data', 'workflow-automation'),
                'icon' => 'dashicons-editor-code',
                'color' => '#FF9800',
                'description' => __('Transform and manipulate data', 'workflow-automation')
            ),
            'wordpress' => array(
                'label' => __('WordPress', 'workflow-automation'),
                'icon' => 'dashicons-wordpress',
                'color' => '#21759B',
                'description' => __('WordPress-specific actions', 'workflow-automation')
            )
        );
    }
}