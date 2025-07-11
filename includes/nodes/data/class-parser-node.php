<?php
/**
 * Parser Node
 *
 * @link       https://opsguide.com
 * @since      1.0.0
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes/nodes/data
 */

/**
 * Parser node class
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes/nodes/data
 * @author     OpsGuide Team <support@opsguide.com>
 */
class WA_Parser_Node extends WA_Abstract_Node {

    /**
     * Get the node type
     *
     * @since    1.0.0
     * @return   string
     */
    public function get_type() {
        return 'parser';
    }

    /**
     * Get node options
     *
     * @since    1.0.0
     * @return   array
     */
    public function get_options() {
        return array(
            'label' => __('Parse Data', 'workflow-automation'),
            'description' => __('Parse structured data from various formats', 'workflow-automation'),
            'icon' => 'dashicons-media-code',
            'category' => 'data',
            'can_be_start' => false
        );
    }

    /**
     * Get settings fields
     *
     * @since    1.0.0
     * @return   array
     */
    public function get_settings_fields() {
        return array(
            array(
                'key' => 'parse_type',
                'label' => __('Parse Type', 'workflow-automation'),
                'type' => 'select',
                'required' => true,
                'default' => 'json',
                'options' => array(
                    'json' => __('JSON', 'workflow-automation'),
                    'xml' => __('XML', 'workflow-automation'),
                    'csv' => __('CSV', 'workflow-automation'),
                    'html' => __('HTML', 'workflow-automation'),
                    'markdown' => __('Markdown', 'workflow-automation'),
                    'yaml' => __('YAML', 'workflow-automation'),
                    'ini' => __('INI', 'workflow-automation'),
                    'url' => __('URL/Query String', 'workflow-automation'),
                    'email' => __('Email Headers', 'workflow-automation'),
                    'regex' => __('Regular Expression', 'workflow-automation'),
                    'xpath' => __('XPath', 'workflow-automation'),
                    'jsonpath' => __('JSONPath', 'workflow-automation')
                ),
                'description' => __('Type of data to parse', 'workflow-automation')
            ),
            array(
                'key' => 'source_path',
                'label' => __('Source Path', 'workflow-automation'),
                'type' => 'text',
                'placeholder' => 'data.raw_content',
                'description' => __('Path to the data to parse. Leave empty to use entire input.', 'workflow-automation')
            ),
            array(
                'key' => 'output_path',
                'label' => __('Output Path', 'workflow-automation'),
                'type' => 'text',
                'placeholder' => 'parsed_data',
                'description' => __('Where to store the parsed result', 'workflow-automation')
            ),
            
            // JSON Options
            array(
                'key' => 'json_options',
                'label' => __('JSON Options', 'workflow-automation'),
                'type' => 'group',
                'fields' => array(
                    array(
                        'key' => 'depth',
                        'label' => __('Max Depth', 'workflow-automation'),
                        'type' => 'number',
                        'default' => 512,
                        'description' => __('Maximum depth for parsing nested structures', 'workflow-automation')
                    ),
                    array(
                        'key' => 'bigint_as_string',
                        'label' => __('Big Integers as String', 'workflow-automation'),
                        'type' => 'checkbox',
                        'default' => false,
                        'description' => __('Parse large integers as strings to prevent precision loss', 'workflow-automation')
                    )
                ),
                'condition' => array(
                    'field' => 'parse_type',
                    'operator' => '==',
                    'value' => 'json'
                )
            ),
            
            // XML Options
            array(
                'key' => 'xml_options',
                'label' => __('XML Options', 'workflow-automation'),
                'type' => 'group',
                'fields' => array(
                    array(
                        'key' => 'simple_xml',
                        'label' => __('Use SimpleXML', 'workflow-automation'),
                        'type' => 'checkbox',
                        'default' => true,
                        'description' => __('Use SimpleXML for parsing (recommended)', 'workflow-automation')
                    ),
                    array(
                        'key' => 'preserve_attributes',
                        'label' => __('Preserve Attributes', 'workflow-automation'),
                        'type' => 'checkbox',
                        'default' => true,
                        'description' => __('Keep XML attributes in parsed data', 'workflow-automation')
                    ),
                    array(
                        'key' => 'namespace_prefix',
                        'label' => __('Namespace Prefix', 'workflow-automation'),
                        'type' => 'text',
                        'placeholder' => 'ns',
                        'description' => __('Prefix for namespace elements', 'workflow-automation')
                    )
                ),
                'condition' => array(
                    'field' => 'parse_type',
                    'operator' => '==',
                    'value' => 'xml'
                )
            ),
            
            // CSV Options
            array(
                'key' => 'csv_options',
                'label' => __('CSV Options', 'workflow-automation'),
                'type' => 'group',
                'fields' => array(
                    array(
                        'key' => 'delimiter',
                        'label' => __('Delimiter', 'workflow-automation'),
                        'type' => 'text',
                        'default' => ',',
                        'description' => __('Field delimiter character', 'workflow-automation')
                    ),
                    array(
                        'key' => 'enclosure',
                        'label' => __('Enclosure', 'workflow-automation'),
                        'type' => 'text',
                        'default' => '"',
                        'description' => __('Field enclosure character', 'workflow-automation')
                    ),
                    array(
                        'key' => 'escape',
                        'label' => __('Escape Character', 'workflow-automation'),
                        'type' => 'text',
                        'default' => '\\',
                        'description' => __('Escape character', 'workflow-automation')
                    ),
                    array(
                        'key' => 'has_header',
                        'label' => __('First Row is Header', 'workflow-automation'),
                        'type' => 'checkbox',
                        'default' => true,
                        'description' => __('Use first row as column names', 'workflow-automation')
                    ),
                    array(
                        'key' => 'column_names',
                        'label' => __('Column Names', 'workflow-automation'),
                        'type' => 'textarea',
                        'rows' => 3,
                        'placeholder' => "id\nname\nemail\nstatus",
                        'description' => __('Manual column names (one per line) if no header row', 'workflow-automation')
                    )
                ),
                'condition' => array(
                    'field' => 'parse_type',
                    'operator' => '==',
                    'value' => 'csv'
                )
            ),
            
            // HTML Options
            array(
                'key' => 'html_options',
                'label' => __('HTML Options', 'workflow-automation'),
                'type' => 'group',
                'fields' => array(
                    array(
                        'key' => 'selector',
                        'label' => __('CSS Selector', 'workflow-automation'),
                        'type' => 'text',
                        'placeholder' => '.content, #main-text, table.data',
                        'description' => __('CSS selector to extract specific elements', 'workflow-automation')
                    ),
                    array(
                        'key' => 'extract_text',
                        'label' => __('Extract Text Only', 'workflow-automation'),
                        'type' => 'checkbox',
                        'default' => false,
                        'description' => __('Extract text content without HTML tags', 'workflow-automation')
                    ),
                    array(
                        'key' => 'extract_links',
                        'label' => __('Extract Links', 'workflow-automation'),
                        'type' => 'checkbox',
                        'default' => false,
                        'description' => __('Extract all links (href attributes)', 'workflow-automation')
                    ),
                    array(
                        'key' => 'extract_images',
                        'label' => __('Extract Images', 'workflow-automation'),
                        'type' => 'checkbox',
                        'default' => false,
                        'description' => __('Extract all image URLs (src attributes)', 'workflow-automation')
                    ),
                    array(
                        'key' => 'extract_meta',
                        'label' => __('Extract Meta Tags', 'workflow-automation'),
                        'type' => 'checkbox',
                        'default' => false,
                        'description' => __('Extract meta tag information', 'workflow-automation')
                    )
                ),
                'condition' => array(
                    'field' => 'parse_type',
                    'operator' => '==',
                    'value' => 'html'
                )
            ),
            
            // Regex Options
            array(
                'key' => 'regex_options',
                'label' => __('Regex Options', 'workflow-automation'),
                'type' => 'group',
                'fields' => array(
                    array(
                        'key' => 'pattern',
                        'label' => __('Pattern', 'workflow-automation'),
                        'type' => 'text',
                        'required' => true,
                        'placeholder' => '/(\w+)@(\w+\.\w+)/',
                        'description' => __('Regular expression pattern with capture groups', 'workflow-automation')
                    ),
                    array(
                        'key' => 'flags',
                        'label' => __('Flags', 'workflow-automation'),
                        'type' => 'text',
                        'placeholder' => 'i',
                        'description' => __('Regex flags (i=case insensitive, m=multiline, s=dotall)', 'workflow-automation')
                    ),
                    array(
                        'key' => 'match_all',
                        'label' => __('Match All', 'workflow-automation'),
                        'type' => 'checkbox',
                        'default' => true,
                        'description' => __('Find all matches instead of just the first', 'workflow-automation')
                    ),
                    array(
                        'key' => 'group_names',
                        'label' => __('Group Names', 'workflow-automation'),
                        'type' => 'textarea',
                        'rows' => 3,
                        'placeholder' => "username\ndomain",
                        'description' => __('Names for capture groups (one per line)', 'workflow-automation')
                    )
                ),
                'condition' => array(
                    'field' => 'parse_type',
                    'operator' => '==',
                    'value' => 'regex'
                )
            ),
            
            // URL Options
            array(
                'key' => 'url_options',
                'label' => __('URL Options', 'workflow-automation'),
                'type' => 'group',
                'fields' => array(
                    array(
                        'key' => 'parse_components',
                        'label' => __('Parse Components', 'workflow-automation'),
                        'type' => 'checkbox',
                        'default' => true,
                        'description' => __('Parse URL into components (scheme, host, path, etc.)', 'workflow-automation')
                    ),
                    array(
                        'key' => 'parse_query',
                        'label' => __('Parse Query String', 'workflow-automation'),
                        'type' => 'checkbox',
                        'default' => true,
                        'description' => __('Parse query string into key-value pairs', 'workflow-automation')
                    ),
                    array(
                        'key' => 'decode_values',
                        'label' => __('URL Decode Values', 'workflow-automation'),
                        'type' => 'checkbox',
                        'default' => true,
                        'description' => __('Decode URL-encoded values', 'workflow-automation')
                    )
                ),
                'condition' => array(
                    'field' => 'parse_type',
                    'operator' => '==',
                    'value' => 'url'
                )
            ),
            
            // Error Handling
            array(
                'key' => 'error_handling',
                'label' => __('Error Handling', 'workflow-automation'),
                'type' => 'select',
                'default' => 'stop',
                'options' => array(
                    'stop' => __('Stop on Error', 'workflow-automation'),
                    'null' => __('Return Null', 'workflow-automation'),
                    'original' => __('Return Original', 'workflow-automation'),
                    'partial' => __('Return Partial Result', 'workflow-automation')
                ),
                'description' => __('How to handle parsing errors', 'workflow-automation')
            )
        );
    }

    /**
     * Execute the node
     *
     * @since    1.0.0
     * @param    array    $context         The execution context
     * @param    mixed    $previous_data   Data from previous node
     * @return   mixed
     */
    public function execute($context, $previous_data) {
        $parse_type = $this->get_setting('parse_type', 'json');
        $source_path = $this->get_setting('source_path', '');
        $output_path = $this->get_setting('output_path', 'parsed_data');
        $error_handling = $this->get_setting('error_handling', 'stop');
        
        // Get source data
        $source_data = $previous_data;
        if (!empty($source_path)) {
            $source_data = $this->get_value_by_path($previous_data, $source_path);
        }
        
        if (empty($source_data)) {
            $this->log('No data to parse');
            return $previous_data;
        }
        
        try {
            // Parse based on type
            $parsed = null;
            switch ($parse_type) {
                case 'json':
                    $parsed = $this->parse_json($source_data);
                    break;
                    
                case 'xml':
                    $parsed = $this->parse_xml($source_data);
                    break;
                    
                case 'csv':
                    $parsed = $this->parse_csv($source_data);
                    break;
                    
                case 'html':
                    $parsed = $this->parse_html($source_data);
                    break;
                    
                case 'markdown':
                    $parsed = $this->parse_markdown($source_data);
                    break;
                    
                case 'yaml':
                    $parsed = $this->parse_yaml($source_data);
                    break;
                    
                case 'ini':
                    $parsed = $this->parse_ini($source_data);
                    break;
                    
                case 'url':
                    $parsed = $this->parse_url($source_data);
                    break;
                    
                case 'email':
                    $parsed = $this->parse_email($source_data);
                    break;
                    
                case 'regex':
                    $parsed = $this->parse_regex($source_data);
                    break;
                    
                case 'xpath':
                    $parsed = $this->parse_xpath($source_data);
                    break;
                    
                case 'jsonpath':
                    $parsed = $this->parse_jsonpath($source_data);
                    break;
                    
                default:
                    throw new Exception('Unknown parse type: ' . $parse_type);
            }
            
            $this->log(sprintf('Successfully parsed %s data', $parse_type));
            
        } catch (Exception $e) {
            $this->log('Parse error: ' . $e->getMessage());
            
            switch ($error_handling) {
                case 'stop':
                    throw $e;
                    
                case 'null':
                    $parsed = null;
                    break;
                    
                case 'original':
                    $parsed = $source_data;
                    break;
                    
                case 'partial':
                    // Return whatever was parsed before error
                    if (!isset($parsed)) {
                        $parsed = null;
                    }
                    break;
            }
        }
        
        // Prepare output
        $result = is_array($previous_data) ? $previous_data : array();
        
        if (!empty($output_path)) {
            $this->set_value_by_path($result, $output_path, $parsed);
        } else {
            $result['parsed_data'] = $parsed;
        }
        
        // Add metadata
        $result['_parse_metadata'] = array(
            'type' => $parse_type,
            'source_length' => is_string($source_data) ? strlen($source_data) : 0,
            'timestamp' => current_time('mysql')
        );
        
        return $result;
    }

    /**
     * Parse JSON
     *
     * @since    1.0.0
     * @param    string    $data    JSON string
     * @return   mixed
     */
    private function parse_json($data) {
        if (!is_string($data)) {
            throw new Exception('JSON parser expects string input');
        }
        
        $options = $this->get_setting('json_options', array());
        $depth = intval($options['depth'] ?? 512);
        $bigint_as_string = $options['bigint_as_string'] ?? false;
        
        $flags = 0;
        if ($bigint_as_string) {
            $flags |= JSON_BIGINT_AS_STRING;
        }
        
        $result = json_decode($data, true, $depth, $flags);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('JSON parse error: ' . json_last_error_msg());
        }
        
        return $result;
    }

    /**
     * Parse XML
     *
     * @since    1.0.0
     * @param    string    $data    XML string
     * @return   array
     */
    private function parse_xml($data) {
        if (!is_string($data)) {
            throw new Exception('XML parser expects string input');
        }
        
        $options = $this->get_setting('xml_options', array());
        $use_simple_xml = $options['simple_xml'] ?? true;
        $preserve_attributes = $options['preserve_attributes'] ?? true;
        
        // Disable XML errors
        $old_setting = libxml_use_internal_errors(true);
        
        try {
            if ($use_simple_xml) {
                $xml = simplexml_load_string($data);
                
                if ($xml === false) {
                    $errors = libxml_get_errors();
                    $error_msg = !empty($errors) ? $errors[0]->message : 'Unknown XML error';
                    throw new Exception('XML parse error: ' . $error_msg);
                }
                
                // Convert to array
                $result = $this->xml_to_array($xml, $preserve_attributes);
            } else {
                // Use DOMDocument for more complex parsing
                $dom = new DOMDocument();
                if (!$dom->loadXML($data)) {
                    $errors = libxml_get_errors();
                    $error_msg = !empty($errors) ? $errors[0]->message : 'Unknown XML error';
                    throw new Exception('XML parse error: ' . $error_msg);
                }
                
                $result = $this->dom_to_array($dom->documentElement, $preserve_attributes);
            }
            
            return $result;
            
        } finally {
            libxml_use_internal_errors($old_setting);
        }
    }

    /**
     * Parse CSV
     *
     * @since    1.0.0
     * @param    string    $data    CSV string
     * @return   array
     */
    private function parse_csv($data) {
        if (!is_string($data)) {
            throw new Exception('CSV parser expects string input');
        }
        
        $options = $this->get_setting('csv_options', array());
        $delimiter = $options['delimiter'] ?? ',';
        $enclosure = $options['enclosure'] ?? '"';
        $escape = $options['escape'] ?? '\\';
        $has_header = $options['has_header'] ?? true;
        $column_names = array_filter(array_map('trim', explode("\n", $options['column_names'] ?? '')));
        
        // Parse CSV
        $result = array();
        $temp = fopen('php://temp', 'r+');
        fwrite($temp, $data);
        rewind($temp);
        
        $headers = array();
        $row_index = 0;
        
        while (($row = fgetcsv($temp, 0, $delimiter, $enclosure, $escape)) !== false) {
            if ($row_index === 0 && $has_header) {
                $headers = $row;
            } else {
                if (!empty($headers)) {
                    // Use headers as keys
                    $assoc_row = array();
                    foreach ($row as $i => $value) {
                        $key = isset($headers[$i]) ? $headers[$i] : $i;
                        $assoc_row[$key] = $value;
                    }
                    $result[] = $assoc_row;
                } elseif (!empty($column_names)) {
                    // Use provided column names
                    $assoc_row = array();
                    foreach ($row as $i => $value) {
                        $key = isset($column_names[$i]) ? $column_names[$i] : $i;
                        $assoc_row[$key] = $value;
                    }
                    $result[] = $assoc_row;
                } else {
                    // Use numeric indices
                    $result[] = $row;
                }
            }
            $row_index++;
        }
        
        fclose($temp);
        
        return $result;
    }

    /**
     * Parse HTML
     *
     * @since    1.0.0
     * @param    string    $data    HTML string
     * @return   array
     */
    private function parse_html($data) {
        if (!is_string($data)) {
            throw new Exception('HTML parser expects string input');
        }
        
        $options = $this->get_setting('html_options', array());
        $selector = $options['selector'] ?? '';
        $extract_text = $options['extract_text'] ?? false;
        $extract_links = $options['extract_links'] ?? false;
        $extract_images = $options['extract_images'] ?? false;
        $extract_meta = $options['extract_meta'] ?? false;
        
        // Create DOMDocument
        $dom = new DOMDocument();
        
        // Suppress HTML parsing warnings
        $old_setting = libxml_use_internal_errors(true);
        
        try {
            // Load HTML with proper encoding
            $dom->loadHTML('<?xml encoding="UTF-8">' . $data, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            
            $result = array();
            
            // Extract based on selector
            if (!empty($selector)) {
                $xpath = new DOMXPath($dom);
                
                // Convert CSS selector to XPath (basic conversion)
                $xpath_query = $this->css_to_xpath($selector);
                $elements = $xpath->query($xpath_query);
                
                $selected = array();
                foreach ($elements as $element) {
                    if ($extract_text) {
                        $selected[] = trim($element->textContent);
                    } else {
                        $selected[] = $dom->saveHTML($element);
                    }
                }
                
                $result['selected'] = $selected;
            }
            
            // Extract all text
            if ($extract_text && empty($selector)) {
                $result['text'] = trim($dom->textContent);
            }
            
            // Extract links
            if ($extract_links) {
                $links = array();
                $link_elements = $dom->getElementsByTagName('a');
                foreach ($link_elements as $link) {
                    $href = $link->getAttribute('href');
                    if (!empty($href)) {
                        $links[] = array(
                            'href' => $href,
                            'text' => trim($link->textContent),
                            'title' => $link->getAttribute('title')
                        );
                    }
                }
                $result['links'] = $links;
            }
            
            // Extract images
            if ($extract_images) {
                $images = array();
                $img_elements = $dom->getElementsByTagName('img');
                foreach ($img_elements as $img) {
                    $src = $img->getAttribute('src');
                    if (!empty($src)) {
                        $images[] = array(
                            'src' => $src,
                            'alt' => $img->getAttribute('alt'),
                            'title' => $img->getAttribute('title'),
                            'width' => $img->getAttribute('width'),
                            'height' => $img->getAttribute('height')
                        );
                    }
                }
                $result['images'] = $images;
            }
            
            // Extract meta tags
            if ($extract_meta) {
                $meta_data = array();
                $meta_elements = $dom->getElementsByTagName('meta');
                foreach ($meta_elements as $meta) {
                    $name = $meta->getAttribute('name') ?: $meta->getAttribute('property');
                    $content = $meta->getAttribute('content');
                    if (!empty($name) && !empty($content)) {
                        $meta_data[$name] = $content;
                    }
                }
                $result['meta'] = $meta_data;
            }
            
            // If no specific extraction, return cleaned HTML
            if (empty($result)) {
                $result = $dom->saveHTML();
            }
            
            return $result;
            
        } finally {
            libxml_use_internal_errors($old_setting);
        }
    }

    /**
     * Parse URL
     *
     * @since    1.0.0
     * @param    string    $data    URL string
     * @return   array
     */
    private function parse_url($data) {
        if (!is_string($data)) {
            throw new Exception('URL parser expects string input');
        }
        
        $options = $this->get_setting('url_options', array());
        $parse_components = $options['parse_components'] ?? true;
        $parse_query = $options['parse_query'] ?? true;
        $decode_values = $options['decode_values'] ?? true;
        
        $result = array();
        
        // Check if it's just a query string
        if (strpos($data, '?') === 0 || strpos($data, '&') !== false && strpos($data, '://') === false) {
            // Parse as query string only
            $query_string = ltrim($data, '?');
            parse_str($query_string, $query_params);
            
            if ($decode_values) {
                array_walk_recursive($query_params, function(&$value) {
                    $value = urldecode($value);
                });
            }
            
            return $query_params;
        }
        
        // Parse full URL
        if ($parse_components) {
            $components = parse_url($data);
            
            if ($components === false) {
                throw new Exception('Invalid URL format');
            }
            
            $result = $components;
        }
        
        // Parse query string
        if ($parse_query && isset($components['query'])) {
            parse_str($components['query'], $query_params);
            
            if ($decode_values) {
                array_walk_recursive($query_params, function(&$value) {
                    $value = urldecode($value);
                });
            }
            
            $result['query_params'] = $query_params;
        }
        
        return $result;
    }

    /**
     * Parse regular expression
     *
     * @since    1.0.0
     * @param    string    $data    Input string
     * @return   array
     */
    private function parse_regex($data) {
        if (!is_string($data)) {
            throw new Exception('Regex parser expects string input');
        }
        
        $options = $this->get_setting('regex_options', array());
        $pattern = $options['pattern'] ?? '';
        $flags = $options['flags'] ?? '';
        $match_all = $options['match_all'] ?? true;
        $group_names = array_filter(array_map('trim', explode("\n", $options['group_names'] ?? '')));
        
        if (empty($pattern)) {
            throw new Exception('Regex pattern is required');
        }
        
        // Ensure pattern has delimiters
        if (!preg_match('/^[\/\#\~\@\%\^\&\*\+\-\_\=\!\?\<\>\|\\\]/', $pattern)) {
            $pattern = '/' . $pattern . '/' . $flags;
        }
        
        $result = array();
        
        if ($match_all) {
            if (preg_match_all($pattern, $data, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $match_result = array();
                    
                    // Full match
                    $match_result['match'] = $match[0];
                    
                    // Capture groups
                    if (count($match) > 1) {
                        $groups = array();
                        for ($i = 1; $i < count($match); $i++) {
                            $group_name = isset($group_names[$i - 1]) ? $group_names[$i - 1] : 'group_' . $i;
                            $groups[$group_name] = $match[$i];
                        }
                        $match_result['groups'] = $groups;
                    }
                    
                    $result[] = $match_result;
                }
            }
        } else {
            if (preg_match($pattern, $data, $match)) {
                // Full match
                $result['match'] = $match[0];
                
                // Capture groups
                if (count($match) > 1) {
                    $groups = array();
                    for ($i = 1; $i < count($match); $i++) {
                        $group_name = isset($group_names[$i - 1]) ? $group_names[$i - 1] : 'group_' . $i;
                        $groups[$group_name] = $match[$i];
                    }
                    $result['groups'] = $groups;
                }
            }
        }
        
        return $result;
    }

    /**
     * Convert SimpleXML to array
     *
     * @since    1.0.0
     * @param    SimpleXMLElement    $xml                  XML element
     * @param    bool                $preserve_attributes  Whether to preserve attributes
     * @return   array
     */
    private function xml_to_array($xml, $preserve_attributes = true) {
        $array = array();
        
        // Handle attributes
        if ($preserve_attributes) {
            foreach ($xml->attributes() as $key => $value) {
                $array['@attributes'][$key] = (string)$value;
            }
        }
        
        // Handle child elements
        foreach ($xml->children() as $key => $value) {
            if (count($value->children()) == 0 && count($value->attributes()) == 0) {
                // Simple element
                $array[$key] = (string)$value;
            } else {
                // Complex element
                $array[$key][] = $this->xml_to_array($value, $preserve_attributes);
            }
        }
        
        // Handle text content
        $text = trim((string)$xml);
        if (strlen($text) > 0) {
            if (count($array) > 0) {
                $array['@text'] = $text;
            } else {
                return $text;
            }
        }
        
        return $array;
    }

    /**
     * Convert CSS selector to XPath (basic conversion)
     *
     * @since    1.0.0
     * @param    string    $selector    CSS selector
     * @return   string
     */
    private function css_to_xpath($selector) {
        // Very basic CSS to XPath conversion
        $xpath = '//' . str_replace(
            array('#', '.', ' ', '>'),
            array('[@id="', '[@class="', '//', '/'),
            $selector
        );
        
        // Close attribute selectors
        $xpath = preg_replace('/\[@(id|class)="([^"]+)"/', '[@$1="$2"]', $xpath);
        
        return $xpath;
    }

    /**
     * Helper methods for get/set value by path
     */
    private function get_value_by_path($data, $path) {
        // Same implementation as in other nodes
        if (empty($path)) {
            return $data;
        }
        
        $parts = explode('.', $path);
        $current = $data;
        
        foreach ($parts as $part) {
            if (is_array($current) && isset($current[$part])) {
                $current = $current[$part];
            } elseif (is_object($current) && isset($current->$part)) {
                $current = $current->$part;
            } else {
                return null;
            }
        }
        
        return $current;
    }

    private function set_value_by_path(&$data, $path, $value) {
        // Same implementation as in other nodes
        if (empty($path)) {
            $data = $value;
            return;
        }
        
        $parts = explode('.', $path);
        $current = &$data;
        
        for ($i = 0; $i < count($parts); $i++) {
            $part = $parts[$i];
            $is_last = ($i === count($parts) - 1);
            
            if ($is_last) {
                $current[$part] = $value;
            } else {
                if (!isset($current[$part])) {
                    $current[$part] = array();
                }
                $current = &$current[$part];
            }
        }
    }

    /**
     * Validate settings
     *
     * @since    1.0.0
     * @return   bool|WP_Error
     */
    public function validate_settings() {
        $parse_type = $this->get_setting('parse_type', '');
        
        if (empty($parse_type)) {
            return new WP_Error('missing_parse_type', __('Parse type is required', 'workflow-automation'));
        }
        
        // Validate based on parse type
        if ($parse_type === 'regex') {
            $options = $this->get_setting('regex_options', array());
            if (empty($options['pattern'])) {
                return new WP_Error('missing_pattern', __('Regex pattern is required', 'workflow-automation'));
            }
        }
        
        return true;
    }
}