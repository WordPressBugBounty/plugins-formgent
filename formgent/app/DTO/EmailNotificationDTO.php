<?php

namespace FormGent\App\DTO;

defined( "ABSPATH" ) || exit;

use FormGent\WpMVC\DTO\DTO;

class EmailNotificationDTO extends DTO {
    private int $id;

    private int $form_id;

    private string $name;

    private string $send_to;

    private string $subject;

    private string $body;

    private ?string $cc = null;

    private ?string $bcc = null;

    private ?string $reply_to = null;

    private ?string $from_name = null;

    private ?string $from_email = null;

    private string $status;

    private string $condition_type;

    private int $condition_status;

    private array $conditions;

    /**
     * Get the value of id
     *
     * @return int
     */
    public function get_id(): int {
        return $this->id;
    }

    /**
     * Set the value of id
     *
     * @param int $id
     *
     * @return self
     */
    public function set_id( int $id ): self {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of form_id
     *
     * @return int
     */
    public function get_form_id(): int {
        return $this->form_id;
    }

    /**
     * Set the value of form_id
     *
     * @param int $form_id
     *
     * @return self
     */
    public function set_form_id( int $form_id ): self {
        $this->form_id = $form_id;

        return $this;
    }

    /**
     * Get the value of name
     *
     * @return string
     */
    public function get_name(): string {
        return $this->name;
    }

    /**
     * Set the value of name
     *
     * @param string $name
     *
     * @return self
     */
    public function set_name( string $name ): self {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of send_to
     *
     * @return string
     */
    public function get_send_to(): string {
        return $this->send_to;
    }

    /**
     * Set the value of send_to
     *
     * @param string $send_to
     *
     * @return self
     */
    public function set_send_to( string $send_to ): self {
        $this->send_to = $send_to;

        return $this;
    }

    /**
     * Get the value of subject
     *
     * @return string
     */
    public function get_subject(): string {
        return $this->subject;
    }

    /**
     * Set the value of subject
     *
     * @param string $subject
     *
     * @return self
     */
    public function set_subject( string $subject ): self {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get the value of body
     *
     * @return string
     */
    public function get_body(): string {
        return $this->body;
    }

    /**
     * Set the value of body
     *
     * @param string $body
     *
     * @return self
     */
    public function set_body( string $body ): self {
        $this->body = $body;

        return $this;
    }

    /**
     * Get the value of cc
     *
     * @return ?string
     */
    public function get_cc(): ?string {
        return $this->cc;
    }

    /**
     * Set the value of cc
     *
     * @param ?string $cc
     *
     * @return self
     */
    public function set_cc( ?string $cc ): self {
        $this->cc = $cc;

        return $this;
    }

    /**
     * Get the value of bcc
     *
     * @return ?string
     */
    public function get_bcc(): ?string {
        return $this->bcc;
    }

    /**
     * Set the value of bcc
     *
     * @param ?string $bcc
     *
     * @return self
     */
    public function set_bcc( ?string $bcc ): self {
        $this->bcc = $bcc;

        return $this;
    }

    /**
     * Get the value of reply_to
     *
     * @return ?string
     */
    public function get_reply_to(): ?string {
        return $this->reply_to;
    }

    /**
     * Set the value of reply_to
     *
     * @param ?string $reply_to
     *
     * @return self
     */
    public function set_reply_to( ?string $reply_to ): self {
        $this->reply_to = $reply_to;

        return $this;
    }

    /**
     * Get the value of from_name
     *
     * @return ?string
     */
    public function get_from_name(): ?string {
        return $this->from_name;
    }

    /**
     * Set the value of from_name
     *
     * @param ?string $from_name
     *
     * @return self
     */
    public function set_from_name( ?string $from_name ): self {
        $this->from_name = $from_name;

        return $this;
    }

    /**
     * Get the value of from_email
     *
     * @return ?string
     */
    public function get_from_email(): ?string {
        return $this->from_email;
    }

    /**
     * Set the value of from_email
     *
     * @param ?string $from_email
     *
     * @return self
     */
    public function set_from_email( ?string $from_email ): self {
        $this->from_email = $from_email;

        return $this;
    }

    /**
     * Get the value of status
     *
     * @return string
     */
    public function get_status(): string {
        return $this->status;
    }

    /**
     * Set the value of status
     *
     * @param string $status
     *
     * @return self
     */
    public function set_status( string $status ): self {
        $this->status = $status;

        return $this;
    }

    /**
     * Get the value of condition_type
     *
     * @return string
     */
    public function get_condition_type(): string {
        return $this->condition_type;
    }

    /**
     * Set the value of condition_type
     *
     * @param string $condition_type
     *
     * @return self
     */
    public function set_condition_type( string $condition_type ): self {
        $this->condition_type = $condition_type;

        return $this;
    }

    /**
     * Get the value of condition_status
     *
     * @return int
     */
    public function get_condition_status(): int {
        return $this->condition_status;
    }

    /**
     * Set the value of condition_status
     *
     * @param int $condition_status
     *
     * @return self
     */
    public function set_condition_status( int $condition_status ): self {
        $this->condition_status = $condition_status;

        return $this;
    }

    /**
     * Get the value of conditions
     *
     * @return array
     */
    public function get_conditions(): array {
        return $this->conditions;
    }

    /**
     * Set the value of conditions
     *
     * @param array $conditions
     *
     * @return self
     */
    public function set_conditions( array $conditions ): self {
        $this->conditions = $conditions;

        return $this;
    }
}