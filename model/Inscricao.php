<?php

namespace SeminarioTematico\model;

use Illuminate\Database\Schema\Blueprint;
use MocaBonita\tools\eloquent\MbModel;
use SigUema\model\Usuarios;

class Inscricao extends MbModel
{

    protected $table = 'inscricao';

    protected $fillable = ['usuario_id'];

    public function createSchema(Blueprint $table)
    {

        $usuario = new Usuarios;

        $table->increments($this->getKeyName());
        $table->unsignedInteger("usuario_id");
        $table->timestamps();

        $table->foreign('usuario_id')
            ->references($usuario->getKeyName())
            ->on($usuario->getTable())
            ->onDelete('cascade');

    }

    public function usuario()
    {
        return $this->belongsTo(Usuarios::class, "usuario_id");
    }


}