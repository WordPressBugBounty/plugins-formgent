<?php defined( 'ABSPATH' ) || exit;

$context = [
    'name'                => $attributes['name'],
    'highlightFileUpload' => false,
    'uploadedFiles'       => [],
    'maxSizeError'        => false,
    'maxFileError'        => false,
    'maxAllowed'          => $attributes['limit_files'],
    'notAllowed'          => false,
    'maxAllowedReached'   => false,
];

// Check if we're in the editor context
$is_editor = ( ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ||
    ( defined( 'ELEMENTOR_VERSION' ) && method_exists( \Elementor\Plugin::$instance->editor ?? null, 'is_edit_mode' ) && \Elementor\Plugin::$instance->editor->is_edit_mode() ) ||
    ( defined( 'ELEMENTOR_VERSION' ) && method_exists( \Elementor\Plugin::$instance->preview ?? null, 'is_preview_mode' ) && \Elementor\Plugin::$instance->preview->is_preview_mode() ) );

if ( $is_editor ) {
    echo '<style>
        .formgent-upload-container {
            min-height: 150px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            cursor: pointer;
            border-radius: 8px;
            border: 1px dashed var(--formgent-color-gray-300);
            background: var(--formgent-field-background-color);
            width: 100%;
            padding: 20px;
            text-align: center;
        }
        .formgent-upload-container .formgent-upload-area {
            border: 0 none;
        }
        .formgent-upload-container p {
            color: var(--formgent-field-placeholder-color);
            text-align: center;
            font-size: 14px;
            font-weight: 400;
            line-height: 1.6;
            margin: 8px 0 0 0;
        }
        .formgent-upload-button {
            display: inline-flex;
            padding: 8px 12px;
            justify-content: center;
            align-items: center;
            gap: 6px;
            border-radius: 8px;
            background: var(--formgent-color-gray-500, #747c89);
            cursor: pointer;
            color: #fff;
            text-align: center;
            font-size: 13px;
            font-weight: 500;
            transition: 0.3s ease;
            border: 0 none;
            box-shadow: none;
        }
    </style>';
}
?>

<div data-wp-interactive="formgent/form" data-wp-context='<?php echo wp_json_encode( $context ); ?>' data-wp-bind--hidden="state.hideField" class="display-none formgent-field   formgent-field-width-<?php echo esc_attr( number_format( $attributes['block_width'] ) ); ?>">
    <div class="formgent-field-single formgent-field-single--file-upload formgent-field-align-<?php echo esc_attr( $attributes['label_alignment'] ); ?>">
        <?php if ( ! empty( $attributes['label'] ) ) : ?>
            <span
                class= "formgent-field-label formgent-label-align-<?php echo esc_attr( $attributes['label_alignment'] ); ?>"
            >
                <?php echo wp_kses_post( $attributes['label'] ); ?>
                <?php if ( $attributes['required'] ) : ?>
                    <span class="formgent-field-required">
                        *
                    </span>
                <?php endif; ?>
            </span>
        <?php endif; ?>
        <div
            class="formgent-upload-container"
        >
            <label
                for="<?php echo esc_attr( formgent_field_id_prefix( $attributes['id'] ) ); ?>"
                class="formgent-upload-area"
                tabindex="0"
                role="button"
                aria-label="<?php echo esc_attr( $attributes['upload_button_text'] ?: __( 'Upload file', 'formgent' ) ); ?>"
                data-wp-class--formgent-file-upload-highlight="context.highlightFileUpload"
                data-wp-class--formgent-file-upload-disabled="context.maxAllowedReached"
                data-wp-bind--aria-disabled="context.maxAllowedReached"
                data-wp-on--dragenter="actions.fileDragEnter"
                data-wp-on--dragover="actions.fileDragEnter"
                data-wp-on--dragleave="actions.fileDragLeave"
                data-wp-on--drop="actions.fileDrop"
                data-wp-on--keydown="actions.handleUploadAreaKeydown"
            >
                <span class="formgent-upload-button">
                    <?php formgent_render_icon( 'upload' ); ?>
                    <?php echo esc_attr( $attributes['upload_button_text'] ); ?>
                </span>
                <input
                    multiple
                    type="file"
                    class="formgent-file-input"
                    style="display: none;"
                    id="<?php echo esc_attr( formgent_field_id_prefix( $attributes['id'] ) ); ?>"
                    data-wp-on--change="actions.onChangeFile"
                    data-wp-bind--disabled="context.maxAllowedReached"
                />

                <!-- Hidden validation input for required field validation -->
                <input
                    type="hidden"
                    class="formgent-validate-field-input"
                    name="<?php echo esc_attr( $attributes['name'] ); ?>"
                    value=""
                />

                <p><?php echo wp_kses_post( $attributes['upload_text'] ); ?></p>

                <?php if ( $attributes['is_limit_size'] ) : ?>
                    <p><?php echo wp_kses_post( str_replace( '{file_size}', $attributes['limit_size'], $attributes['limit_text'] ) ); ?></p>
                <?php endif; ?>

                <?php if ( $attributes['is_limit_files'] ) : ?>
                    <p><?php echo wp_kses_post( str_replace( '{file_limit}', $attributes['limit_files'], $attributes['limit_files_text'] ) ); ?></p>
                <?php endif; ?>
            </label>

            <span class="formgent-warning-message" data-wp-bind--hidden="!context.maxSizeError">
                <?php echo wp_kses_post( str_replace( '{file_size}', $attributes['limit_size'], $attributes['limit_text'] ) ); ?>
            </span>
            <span class="formgent-warning-message" data-wp-bind--hidden="!context.maxFileError">
                <?php echo wp_kses_post( str_replace( '{file_limit}', $attributes['limit_files'], $attributes['limit_files_text'] ) ); ?>
            </span>
            <span class="formgent-warning-message" data-wp-bind--hidden="!context.notAllowed">
                <?php
                echo esc_html(
                    'File type not allowed. Allowed file types: ' . implode( ', ', $attributes['allowed_types'] )
                );
                ?>
            </span>


            <div class="formgent-file-preview" data-wp-bind--hidden="!state.hasFiles">
                <ul class="formgent-preview-items">
                    <template data-wp-each="context.uploadedFiles">
                        <li class="formgent-preview-container">
                            <div class="formgent-file-preview-media">
                                <div>
                                    <img
                                        data-wp-bind--hidden="!context.item.isImage"
                                        class="formgent-preview-image"
                                        data-wp-bind--src="context.item.src"
                                    />
                                    <video
                                        data-wp-bind--hidden="!context.item.isVideo"
                                        class="formgent-preview-image"
                                        width="120"
                                        height="100"
                                    >
                                        <source data-wp-bind--src="context.item.src">
                                    </video>
                                    <span data-wp-bind--hidden="!context.item.isOther">
                                        <?php formgent_render_icon( 'file-clip', 'general' ); ?>
                                    </span>
                                </div>
                                <p class="formgent-file-name">
                                    <span data-wp-text="context.item.name"></span><br/>
                                    <span data-wp-text="context.item.size"></span>
                                </p>
                                <div
                                    data-wp-bind--hidden="context.item.uploaded"
                                    class="formgent-file-preview-status"
                                >
                                    <span
                                        data-wp-style----formgent-file-upload-progress="context.item.progress"
                                        class="formgent-file-upload-progress"
                                    ></span>
                                    <span
                                        data-wp-text="context.item.progress"
                                        class="formgent-file-upload-progress-text"
                                    >
                                        0%
                                    </span>
                                </div>
                            </div>
                            <div data-wp-bind--hidden="!context.item.uploaded" class="formgent-file-preview-action">
                                <span
                                    class="formgent-file-delete"
                                    tabindex="0"
                                    role="button"
                                    aria-label="<?php echo esc_attr__( 'Delete file', 'formgent' ); ?>"
                                    data-wp-on--click="actions.removeFile"
                                    data-wp-on--keydown="actions.handleRemoveFileKeydown"
                                >
                                    <?php formgent_render_icon( 'trash' ); ?>
                                </span>
                            </div>
                        </li>
                    </template>
                </ul>
            </div>
        </div>
    </div>
    <?php if ( ! empty( $attributes['sub_label'] ) ) : ?>
        <span class="formgent-field-sub-label">
            <?php echo wp_kses_post( $attributes['sub_label'] ); ?>
        </span>
    <?php endif; ?>
</div>
