<?php

namespace FormGent\App\Services\Mcp;

defined( 'ABSPATH' ) || exit;

/**
 * Generates client configuration containing placeholders only.
 */
class ClientConfigService {
    private const BRIDGE_PACKAGE = '@automattic/mcp-wordpress-remote@latest';

    /**
     * @return array<string,mixed>
     */
    public function get( string $endpoint ): array {
        $endpoint = esc_url_raw( $endpoint );
        $server   = [
            'command' => 'npx',
            'args'    => ['-y', self::BRIDGE_PACKAGE],
            'env'     => [
                'WP_API_URL'      => $endpoint,
                'WP_API_USERNAME' => 'wordpress-username',
                'WP_API_PASSWORD' => 'your-application-password',
            ],
        ];

        $json_config = [
            'kind'    => 'json',
            'content' => ['mcpServers' => ['formgent' => $server]],
        ];
        $clients     = array_fill_keys( ['generic', 'claude_desktop', 'claude_code', 'cursor', 'continue'], $json_config );

        // VS Code uses a top-level `servers` object instead of `mcpServers`.
        $clients['vscode'] = [
            'kind'    => 'json',
            'content' => [
                'servers' => [
                    'formgent' => array_merge( ['type' => 'stdio'], $server ),
                ],
            ],
        ];

        $clients['chatgpt_codex'] = [
            'kind' => 'codex',
            'cli'  => $this->codex_cli( $endpoint ),
            'toml' => $this->codex_toml( $endpoint ),
        ];

        return $clients;
    }

    private function codex_cli( string $endpoint ): string {
        $environment = [
            'WP_API_URL=' . $endpoint,
            'WP_API_USERNAME=wordpress-username',
            'WP_API_PASSWORD=your-application-password',
        ];

        return sprintf(
            'codex mcp add formgent --env %1$s --env %2$s --env %3$s -- npx -y %4$s',
            $this->shell_argument( $environment[0] ),
            $this->shell_argument( $environment[1] ),
            $this->shell_argument( $environment[2] ),
            self::BRIDGE_PACKAGE
        );
    }

    private function codex_toml( string $endpoint ): string {
        return implode(
            "\n",
            [
                '[mcp_servers.formgent]',
                'command = "npx"',
                'args = ["-y", "' . self::BRIDGE_PACKAGE . '"]',
                '',
                '[mcp_servers.formgent.env]',
                'WP_API_URL = ' . $this->toml_string( $endpoint ),
                'WP_API_USERNAME = "wordpress-username"',
                'WP_API_PASSWORD = "your-application-password"',
            ]
        );
    }

    private function shell_argument( string $value ): string {
        return "'" . str_replace( "'", "'\"'\"'", $value ) . "'";
    }

    private function toml_string( string $value ): string {
        return '"' . str_replace( ['\\', '"', "\n", "\r", "\t"], ['\\\\', '\\"', '\\n', '\\r', '\\t'], $value ) . '"';
    }
}
