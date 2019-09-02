<?php

namespace App\Http\Controllers;

use Validator;
use App\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MessageController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($receiver_id)
    {
        // Get all messages currently logged in and selected user
        $messages = Message::where(['sender_id' => auth()->user()->id, 'receiver_id' => $receiver_id])->orWhere('sender_id', $receiver_id)->where('receiver_id', auth()->user()->id)->orderBy('id', 'asc')->get();

        return response()->json($messages);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validate form data
        $rules = array(
            'body' => 'required|string',
        );
        $validator = Validator::make ( $request->all(), $rules);
        if ($validator->fails()){
            return response()->json(array('errors'=> $validator->getMessageBag()->toarray()));
        }

        // Create an instance
        $message = new Message();
        $message->sender_id = auth()->user()->id;
        $message->receiver_id = $request->receiver_id;
        $message->body = $request->body;
        $message->save();

        return response()->json($message);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function imageStore(Request $request)
    {
        // Validate form data
        $rules = array(
            'image' => 'required|image|max:1024',
        );
        $validator = Validator::make ( $request->all(), $rules);
        if ($validator->fails()){
            return response()->json(array('errors'=> $validator->getMessageBag()->toarray()));
        }

        if ($request->file('image')->isValid()) {
            //get filename with extension
            $filenamewithextension = $request->file('image')->getClientOriginalName();

            //get filename without extension
            $filename = pathinfo($filenamewithextension, PATHINFO_FILENAME);

            //get file extension
            $extension = $request->file('image')->getClientOriginalExtension();

            //filename to store
            $filenametostore = $filename.'_'.time().'.'.$extension;

            //Upload File
            $request->file('image')->storeAs('public/images', $filenametostore);
        }

        // Create an instance
        $message = new Message();
        $message->sender_id = auth()->user()->id;
        $message->receiver_id = $request->receiver_id;
        $message->image = $filenametostore;
        $message->save();

        return response()->json($message);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Message  $message
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Message $message)
    {
        // Validate form data
        $rules = array(
            'body' => 'required|string',
        );
        $validator = Validator::make ( $request->all(), $rules);
        if ($validator->fails()){
            return response()->json(array('errors'=> $validator->getMessageBag()->toarray()));
        }

        // Find model, apply value then save to DB
        $message = Message::find($message->id);
        $message->body = $request->body;
        $message->save();

        return response()->json($message);
    }
    
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Message  $message
     * @return \Illuminate\Http\Response
     */
    public function destroy(Message $message)
    {
        $message = Message::find($message->id);
        if ($message->image){
            // Delete image from the directory
            Storage::delete('public/images/'.$message->image);
        }
        $message->delete();

        return response()->json();
    }
}
