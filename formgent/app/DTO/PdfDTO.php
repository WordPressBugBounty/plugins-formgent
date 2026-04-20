<?php

namespace FormGent\App\DTO;

defined( 'ABSPATH' ) || exit;

use FormGent\WpMVC\DTO\DTO;

class PdfDTO extends DTO {
    private int $id = 0;

    private int $form_id;

    private string $template_name;

    private ?string $template_type = null;

    private string $content;

    private ?string $paper_size = null;

    private ?string $orientation = null;

    private ?string $direction = null;

    private ?string $password = null;

    public function get_id(): int {
        return $this->id;
    }

    public function set_id( int $id ): self {
        $this->id = $id;

        return $this;
    }

    public function get_form_id(): int {
        return $this->form_id;
    }

    public function set_form_id( int $form_id ): self {
        $this->form_id = $form_id;

        return $this;
    }

    public function get_template_name(): string {
        return $this->template_name;
    }

    public function set_template_name( string $template_name ): self {
        $this->template_name = $template_name;

        return $this;
    }

    public function get_template_type(): ?string {
        return $this->template_type;
    }

    public function set_template_type( ?string $template_type ): self {
        $this->template_type = $template_type;

        return $this;
    }

    public function get_content(): string {
        return $this->content;
    }

    public function set_content( string $content ): self {
        $this->content = $content;

        return $this;
    }

    public function get_paper_size(): ?string {
        return $this->paper_size;
    }

    public function set_paper_size( ?string $paper_size ): self {
        $this->paper_size = $paper_size;

        return $this;
    }

    public function get_orientation(): ?string {
        return $this->orientation;
    }

    public function set_orientation( ?string $orientation ): self {
        $this->orientation = $orientation;

        return $this;
    }

    public function get_direction(): ?string {
        return $this->direction;
    }

    public function set_direction( ?string $direction ): self {
        $this->direction = $direction;

        return $this;
    }

    public function get_password(): ?string {
        return $this->password;
    }

    public function set_password( ?string $password ): self {
        $this->password = $password;

        return $this;
    }
}
