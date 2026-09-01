<?php

namespace FormGent\App\Services\Forms\Contracts;

defined( 'ABSPATH' ) || exit;

/**
 * Narrow extension contract for Pro and third-party MCP form fields.
 */
interface FormFieldAdapterInterface {
    public function get_field_type(): string;

    /** @param array<string,mixed> $schema Closed input field schema. @return array<string,mixed> */
    public function extend_schema( array $schema ): array;

    /**
     * @param array<string,mixed> $normalized Safe normalized field.
     * @param array<string,mixed> $source Raw validated input or registered block attributes.
     * @return array<string,mixed>
     */
    public function normalize( array $normalized, array $source, string $context ): array;

    /**
     * @param array<string,mixed> $block Serialized block structure.
     * @param array<string,mixed> $field Validated normalized field.
     * @return array<string,mixed>
     */
    public function build_block( array $block, array $field ): array;
}
