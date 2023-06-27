<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use PDOException;

class UserController extends Controller
{   
    // 유저 정보 추가
    public function store(Request $request)
    {
        $user = new User([
            'id' => $request -> id,
            'nickname' => 'User'.uniqid(),
        ]);
    
        try {
            $user->saveOrFail();
        } catch (PDOException $e) {
            // PDOException : DB 연결 오류, 쿼리 실행 오류와 같은 DB 작업 시 발생
            return response() -> json(['message' => 'Save user failed'], 500);
        }
    
        return response() -> json(['message' => 'Add user successfully'], 200);
    }

    // 특정 유저의 정보를 반환
    public function show($id)
    {
        // findOrFail 메서드가 예외 발생시 404 반환
        $user = User::findOrFail($id);
        return response() -> json($user -> toArray());
    }

    // 유저 닉네임 수정
    public function update(Request $request)
    {
        $validated = $request->validate([
            // 필수값이며, 중복값이 없어야함 있으면 자동으로 응답 반환
            'id' => 'required',
            'nickname' => 'required'
        ]);

        $user = User::findOrFail($validated['id']);
        $user -> nickname = $validated['nickname'];
        $user -> save();

        return response()-> json(['message' => 'Update user successfully']);
    }
}
