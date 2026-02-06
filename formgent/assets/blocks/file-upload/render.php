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
                                        <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="file-attachment"><path id="Icon (Stroke)" fill-rule="evenodd" clip-rule="evenodd" d="M14.5978 1.66675H25.4022C26.7438 1.66673 27.8511 1.66671 28.753 1.74041C29.6899 1.81695 30.551 1.98122 31.3599 2.39337C32.6143 3.03253 33.6342 4.0524 34.2734 5.30681C34.6855 6.11571 34.8498 6.97688 34.9263 7.91371C35 8.81569 35 9.92295 35 11.2645V11.6667C35 12.5872 34.2538 13.3334 33.3333 13.3334C32.4129 13.3334 31.6667 12.5872 31.6667 11.6667V11.3334C31.6667 9.90578 31.6654 8.9353 31.6041 8.18515C31.5444 7.45445 31.4362 7.08078 31.3034 6.82011C30.9838 6.19291 30.4738 5.68297 29.8466 5.36339C29.586 5.23058 29.2123 5.12237 28.4816 5.06267C27.7315 5.00138 26.761 5.00008 25.3333 5.00008H14.6667C13.239 5.00008 12.2685 5.00138 11.5184 5.06267C10.7877 5.12237 10.414 5.23058 10.1534 5.36339C9.52616 5.68297 9.01622 6.19291 8.69665 6.82012C8.56383 7.08078 8.45562 7.45445 8.39592 8.18515C8.33463 8.9353 8.33333 9.90578 8.33333 11.3334V28.6667C8.33333 30.0944 8.33463 31.0649 8.39592 31.815C8.45562 32.5457 8.56383 32.9194 8.69665 33.18C9.01622 33.8073 9.52616 34.3172 10.1534 34.6368C10.414 34.7696 10.7877 34.8778 11.5184 34.9375C12.2685 34.9988 13.239 35.0001 14.6667 35.0001H20.8333C21.7538 35.0001 22.5 35.7463 22.5 36.6667C22.5 37.5872 21.7538 38.3334 20.8333 38.3334H14.5978C13.2562 38.3334 12.1489 38.3335 11.247 38.2598C10.3101 38.1832 9.44896 38.0189 8.64006 37.6068C7.38565 36.9676 6.36578 35.9478 5.72662 34.6934C5.31447 33.8845 5.1502 33.0233 5.07366 32.0865C4.99996 31.1845 4.99998 30.0772 5 28.7356V11.2646C4.99998 9.92297 4.99996 8.8157 5.07366 7.91371C5.1502 6.97688 5.31447 6.11571 5.72662 5.30681C6.36578 4.0524 7.38565 3.03253 8.64006 2.39337C9.44896 1.98122 10.3101 1.81695 11.247 1.74041C12.149 1.66671 13.2562 1.66673 14.5978 1.66675ZM11.6667 11.6667C11.6667 10.7463 12.4129 10.0001 13.3333 10.0001H26.6667C27.5871 10.0001 28.3333 10.7463 28.3333 11.6667C28.3333 12.5872 27.5871 13.3334 26.6667 13.3334H13.3333C12.4129 13.3334 11.6667 12.5872 11.6667 11.6667ZM11.6667 18.3334C11.6667 17.4129 12.4129 16.6667 13.3333 16.6667H20.8333C21.7538 16.6667 22.5 17.4129 22.5 18.3334C22.5 19.2539 21.7538 20.0001 20.8333 20.0001H13.3333C12.4129 20.0001 11.6667 19.2539 11.6667 18.3334ZM32.5 20.0001C32.0398 20.0001 31.6667 20.3732 31.6667 20.8334V30.0001C31.6667 30.9206 30.9205 31.6667 30 31.6667C29.0795 31.6667 28.3333 30.9206 28.3333 30.0001V20.8334C28.3333 18.5322 30.1988 16.6667 32.5 16.6667C34.8012 16.6667 36.6667 18.5322 36.6667 20.8334V30.0001C36.6667 33.682 33.6819 36.6667 30 36.6667C26.3181 36.6667 23.3333 33.682 23.3333 30.0001V23.3334C23.3333 22.4129 24.0795 21.6667 25 21.6667C25.9205 21.6667 26.6667 22.4129 26.6667 23.3334V30.0001C26.6667 31.841 28.1591 33.3334 30 33.3334C31.841 33.3334 33.3333 31.841 33.3333 30.0001V20.8334C33.3333 20.3732 32.9602 20.0001 32.5 20.0001ZM11.6667 25.0001C11.6667 24.0796 12.4129 23.3334 13.3333 23.3334H19.1667C20.0871 23.3334 20.8333 24.0796 20.8333 25.0001C20.8333 25.9206 20.0871 26.6667 19.1667 26.6667H13.3333C12.4129 26.6667 11.6667 25.9206 11.6667 25.0001Z" fill="currentColor"></path></g></svg>
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
