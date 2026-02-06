<?php

namespace FormGent\App\DTO\Mailchimp;

defined( "ABSPATH" ) || exit;

class FeedDTO extends \FormGent\WpMVC\DTO\DTO {
    private int $id;

    private int $form_id;

    private string $title;

    private string $list_id;

    private ?string $group_id;

    private ?string $group_option_id;

    private array $field_mapping;

    private array $tags;

    private int $double_opt_in;

    private int $resubscribe_existing_contact;

    private int $update_existing_contact;

    private int $mark_as_vip;

    private int $condition_status;

    private string $condition_type;

    private array $conditions;

    private string $status;

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
     * @return self
     */
    public function set_form_id( int $form_id ): self {
        $this->form_id = $form_id;
    
        return $this;
    }

    /**
     * Get the value of title
     *
     * @return string
     */
    public function get_title(): string {
        return $this->title;
    }
    
    /**
     * Set the value of title
     *
     * @param string $title
     * @return self
     */
    public function set_title( string $title ): self {
        $this->title = $title;
    
        return $this;
    }

    /**
     * Get the value of list_id
     *
     * @return string
     */
    public function get_list_id(): string {
        return $this->list_id;
    }
    
    /**
     * Set the value of list_id
     *
     * @param string $list_id
     * @return self
     */
    public function set_list_id( string $list_id ): self {
        $this->list_id = $list_id;
    
        return $this;
    }

    /**
     * Get the value of group_id
     *
     * @return ?string
     */
    public function get_group_id(): ?string {
        return $this->group_id;
    }
    
    /**
     * Set the value of group_id
     *
     * @param ?string $group_id
     * @return self
     */
    public function set_group_id( ?string $group_id ): self {
        $this->group_id = $group_id;
    
        return $this;
    }

    /**
     * Get the value of group_option_id
     *
     * @return ?string
     */
    public function get_group_option_id(): ?string {
        return $this->group_option_id;
    }
    
    /**
     * Set the value of group_option_id
     *
     * @param ?string $group_option_id
     * @return self
     */
    public function set_group_option_id( ?string $group_option_id ): self {
        $this->group_option_id = $group_option_id;
    
        return $this;
    }

    /**
     * Get the value of field_mapping
     *
     * @return array
     */
    public function get_field_mapping(): array {
        return $this->field_mapping;
    }
    
    /**
     * Set the value of field_mapping
     *
     * @param array $field_mapping
     * @return self
     */
    public function set_field_mapping( array $field_mapping ): self {
        $this->field_mapping = $field_mapping;
    
        return $this;
    }

    /**
     * Get the value of tags
     *
     * @return array
     */
    public function get_tags(): array {
        return $this->tags;
    }
    
    /**
     * Set the value of tags
     *
     * @param array $tags
     * @return self
     */
    public function set_tags( array $tags ): self {
        $this->tags = $tags;
    
        return $this;
    }

    /**
     * Get the value of double_opt_in
     *
     * @return int
     */
    public function get_double_opt_in(): int {
        return $this->double_opt_in;
    }
    
    /**
     * Set the value of double_opt_in
     *
     * @param int $double_opt_in
     * @return self
     */
    public function set_double_opt_in( int $double_opt_in ): self {
        $this->double_opt_in = $double_opt_in;
    
        return $this;
    }

    /**
     * Get the value of resubscribe_existing_contact
     *
     * @return int
     */
    public function get_resubscribe_existing_contact(): int {
        return $this->resubscribe_existing_contact;
    }
    
    /**
     * Set the value of resubscribe_existing_contact
     *
     * @param int $resubscribe_existing_contact
     * @return self
     */
    public function set_resubscribe_existing_contact( int $resubscribe_existing_contact ): self {
        $this->resubscribe_existing_contact = $resubscribe_existing_contact;
    
        return $this;
    }

    /**
     * Get the value of update_existing_contact
     *
     * @return int
     */
    public function get_update_existing_contact(): int {
        return $this->update_existing_contact;
    }
    
    /**
     * Set the value of update_existing_contact
     *
     * @param int $update_existing_contact
     * @return self
     */
    public function set_update_existing_contact( int $update_existing_contact ): self {
        $this->update_existing_contact = $update_existing_contact;
    
        return $this;
    }

    /**
     * Get the value of mark_as_vip
     *
     * @return int
     */
    public function get_mark_as_vip(): int {
        return $this->mark_as_vip;
    }
    
    /**
     * Set the value of mark_as_vip
     *
     * @param int $mark_as_vip
     * @return self
     */
    public function set_mark_as_vip( int $mark_as_vip ): self {
        $this->mark_as_vip = $mark_as_vip;
    
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
     * @return self
     */
    public function set_condition_status( int $condition_status ): self {
        $this->condition_status = $condition_status;
    
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
     * @return self
     */
    public function set_condition_type( string $condition_type ): self {
        $this->condition_type = $condition_type;
    
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
     * @return self
     */
    public function set_conditions( array $conditions ): self {
        $this->conditions = $conditions;
    
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
     * @return self
     */
    public function set_status( string $status ): self {
        $this->status = $status;
    
        return $this;
    }
}