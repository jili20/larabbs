<?php

namespace App\Http\Controllers;

use App\Handlers\ImageUploadHandler;
use App\Models\Category;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\TopicRequest;
use Auth;


class TopicsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['index', 'show']]);
    }

	public function index(Request $request, Topic $topic,User $user)
	{
        // $topics = Topic::with('user','category')->paginate(30);
        // $topic = DB::table('topics')->simplePaginate(15);
		$topics = $topic->withOrder($request->order)->paginate(20);
		$active_users = $user->getActiveUsers();
		return view('topics.index', compact('topics','active_users'));
	}

 /*   public function show(Topic $topic)
    {
        return view('topics.show', compact('topic'));
    }*/

    public function show(Request $request, Topic $topic)
    {
        // URL 矫正
        if ( ! empty($topic->slug) && $topic->slug != $request->slug) {
            session()->reflash();
            return redirect($topic->link(), 301);
        }

        return view('topics.show', compact('topic'));
    }

	public function create(Topic $topic)
	{
	    $categories = Category::all();
		return view('topics.create_and_edit', compact('topic','categories'));
	}

/*    public function store(TopicRequest $request, Topic $topic)
    {
        $topic->fill($request->all());
        $topic->user_id = Auth::id();
        $topic->save();

        return redirect()->route('topics.show', $topic->id)->with('success', '帖子创建成功！');
    }*/

    public function store(TopicRequest $request, ImageUploadHandler $uploader, Topic $topic)
    {
        $topic->fill($request->all());
        $topic->user_id = Auth::id();

        if ($request->photo) {
            $result = $uploader->save($request->photo, 'photos', $topic->id);
            if ($result) {
                $topic['photo'] = $result['path'];
            }
        }
        $topic->save();
//        return redirect()->route('topics.show', $topic->id)->with('success', '帖子创建成功！');
        return redirect()->to($topic->link())->with('success', '帖子创建成功！');
    }


    public function edit(Topic $topic)
    {
        $this->authorize('update', $topic);
        $categories = Category::all();
        return view('topics.create_and_edit', compact('topic', 'categories'));
    }


	public function update(TopicRequest $request, Topic $topic)
	{
		$this->authorize('update', $topic);
		$topic->update($request->all());

//		return redirect()->route('topics.show', $topic->id)->with('message', '更新成功！');
		return redirect()->to($topic->link())->with('message', '更新成功！');
	}

/*    public function update(TopicRequest $request,ImageUploadHandler $uploader, Topic $topic)
	{
        dd($request->photo);
		$this->authorize('update', $topic);
        $data = $request->all();
        if ($request->photo) {
            $result = $uploader->save($request->photo, 'photos', $topic->id);
            if ($result) {
                $data['photo'] = $result['path'];
            }
        }

        $topic->update($data);

		return redirect()->route('topics.show', $topic->id)->with('message', 'Updated successfully.');
	}*/


    public function destroy(Topic $topic)
    {
        $this->authorize('destroy', $topic);
        $topic->delete();
//        return redirect()->route('topics.index')->with('success', '成功删除！');
        return [];

    }


    // 上传图片方法
    public function uploadImage(Request $request, ImageUploadHandler $uploader)
{
    // 初始化返回数据，默认是失败的
    $data = [
        'success'   => false,
        'msg'       => '上传失败!',
        'file_path' => ''
    ];
    // 判断是否有上传文件，并赋值给 $file
    if ($file = $request->upload_file) {
        // 保存图片到本地
        $result = $uploader->save($request->upload_file, 'topics', \Auth::id(), 1024);
        // 图片保存成功的话
        if ($result) {
            $data['file_path'] = $result['path'];
            $data['msg']       = "上传成功!";
            $data['success']   = true;
        }
    }
    return $data;
}

}




























