<?php

namespace FormGent\App\Repositories;

defined( 'ABSPATH' ) || exit;

use FormGent\App\DTO\NoteDTO;
use FormGent\App\Models\Note;
use FormGent\App\DTO\NoteReadDTO;

class NoteRepository {
    public function get( NoteReadDTO $dto ) {
        $query = Note::query()->where( 'response_id', $dto->get_response_id() );
        return $query->get();
    }

    public function create( NoteDTO $dto ) {
        return Note::query()->insert_get_id( $dto->to_array() );
    }

    public function update( NoteDTO $dto ) {
        return Note::query()->where( 'id', $dto->get_id() )->where( 'response_id', $dto->get_response_id() )->update(
            [
                'note' => $dto->get_note()
            ]
        );
    }

    public function get_by_id( $id ) {
        return Note::query()->where( 'id', $id )->first();
    }

    public function delete( int $response_id, int $id ) {
        return Note::query()->where( 'response_id', $response_id )->where( 'id', $id )->delete();
    }
}