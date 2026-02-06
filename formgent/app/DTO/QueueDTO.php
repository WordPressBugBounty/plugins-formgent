<?php

namespace FormGent\App\DTO;

defined( "ABSPATH" ) || exit;

use FormGent\WpMVC\DTO\DTO;

class QueueDTO extends DTO {
    private int $id;

    private int $form_id;

    private int $response_id;

    private string $task_type;

    private string $task_id;

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
     * Get the value of response_id
     *
     * @return int
     */
    public function get_response_id(): int {
        return $this->response_id;
    }

    /**
     * Set the value of response_id
     *
     * @param int $response_id 
     *
     * @return self
     */
    public function set_response_id( int $response_id ): self {
        $this->response_id = $response_id;

        return $this;
    }

    /**
     * Get the value of task_type
     *
     * @return string
     */
    public function get_task_type(): string {
        return $this->task_type;
    }

    /**
     * Set the value of task_type
     *
     * @param string $task_type 
     *
     * @return self
     */
    public function set_task_type( string $task_type ): self {
        $this->task_type = $task_type;

        return $this;
    }

    /**
     * Get the value of task_id
     *
     * @return string
     */
    public function get_task_id(): string {
        return $this->task_id;
    }

    /**
     * Set the value of task_id
     *
     * @param string $task_id 
     *
     * @return self
     */
    public function set_task_id( string $task_id ): self {
        $this->task_id = $task_id;

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
}