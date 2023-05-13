<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Wall;
use App\Models\WallLike;

class WallController extends Controller
{


    /**
     * @return array
     */
    public function getAll() : array
    {
        $array = ['error' => ''];

        $user = auth()->user();

        // todos os avisos do mural
        $walls = Wall::all();

        foreach($walls as $wallKey => $wallValue) {

            $walls[$wallKey]['likes'] = 0;
            $walls[$wallKey]['liked'] = false;

            $likes = WallLike::where('id_wall', $wallValue['id'])->count();
            $walls[$wallKey]['likes'] = $likes;

            $meLikes = WallLike::where('id_wall', $wallValue['id'])
                       ->where('id_user', $user['id'])
                       ->count();

            if ($meLikes > 0) {

                $walls[$wallKey]['liked'] = true;

            }


        }

        $array['list'] = $walls;

        return $array;
    }

    public function setWall(Request $request)
    {
        $array = ['error' => ''];

        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'body' => 'required|string'
        ]);

        if (!$validator->fails()) {
            $title = $request->input('title');
            $body = $request->input('body');
            $datecreated = date('Y-m-d H:i:s');

            $newWall = new Wall();
            $newWall->title = $title;
            $newWall->body = $body;
            $newWall->datecreated = $datecreated;
            $newWall->save();

            $array['wall'] = $newWall;
        } else {
            $array['error'] = $validator->errors()->first();
        }


        return $array;
    }

    public function removeWall(int $id)
    {

        $array = ['error' => ''];

        $id = addslashes($id);

        $wall = Wall::where('id', $id);

        $delete = $wall->delete();

        if ($delete) {
            $array['wall_removed'] = $id;
        } else {
            $array['error'] = 'Falha ao remover Aviso';
        }


        return $array;
    }

    /**
     * @param int $id
     *
     * @return array
     */
    public function like(int $id) : array
    {
        $array  = ['error' => ''];

        $user = auth()->user();

        // verifica se o usuário já deu like na postagem
        $meLikes = WallLike::where('id_wall', $id)
                       ->where('id_user', $user['id'])
                       ->count();

        if ($meLikes > 0) {
            // remove o like do usuário
            WallLike::where('id_wall', $id)
                    ->where('id_user', $user['id'])
                    ->delete();

            $array['liked'] = false;
        } else {
            // adiciona o like do usuário

            $newLike = new WallLike();
            $newLike->id_wall = $id;
            $newLike->id_user = $user['id'];
            $newLike->save();

            $array['liked'] = true;
        }

        $array['likes'] = WallLike::where('id_wall', $id)->count();

        return $array;
    }

}
