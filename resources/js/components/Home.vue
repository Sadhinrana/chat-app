<template>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div id="frame">
                        <div id="sidepanel">
                            <div id="profile">
                                <div class="wrap">
                                    <img id="profile-img" src="http://emilcarlsson.se/assets/mikeross.png" class="online" alt="" />
                                    <p>{{ auth.name }}</p>
                                    <i class="fa fa-chevron-down expand-button" aria-hidden="true"></i>
                                    <div id="status-options">
                                        <ul>
                                            <li id="status-online" class="active"><span class="status-circle"></span> <p>Online</p></li>
                                            <li id="status-away"><span class="status-circle"></span> <p>Away</p></li>
                                            <li id="status-busy"><span class="status-circle"></span> <p>Busy</p></li>
                                            <li id="status-offline"><span class="status-circle"></span> <p>Offline</p></li>
                                        </ul>
                                    </div>
                                    <div id="expanded">
                                        <label for="twitter"><i class="fa fa-facebook fa-fw" aria-hidden="true"></i></label>
                                        <input name="twitter" type="text" value="mikeross" />
                                        <label for="twitter"><i class="fa fa-twitter fa-fw" aria-hidden="true"></i></label>
                                        <input name="twitter" type="text" value="ross81" />
                                        <label for="twitter"><i class="fa fa-instagram fa-fw" aria-hidden="true"></i></label>
                                        <input name="twitter" type="text" value="mike.ross" />
                                    </div>
                                </div>
                            </div>
                            <div id="search">
                                <label for=""><i class="fa fa-search" aria-hidden="true"></i></label>
                                <input type="text" placeholder="Search contacts..." />
                            </div>
                            <div id="contacts">
                                <ul>
                                    <li v-for="user in users" class="contact" @click="getUser(user.id, user.name)" :class="{active:selected == user.id}">
                                        <div class="wrap">
                                            <span class="contact-status online" v-if="active.includes(user.id)"></span>
                                            <span class="contact-status busy" v-else=""></span>
                                            <img src="http://emilcarlsson.se/assets/louislitt.png" alt="" />
                                            <div class="meta">
                                                <p class="name">{{ user.name }}</p>
                                                <!--<p class="preview" v-if="selected === user.id">{{ latest_sent_message }}</p>-->
                                                <p class="preview" v-if="sender_id === user.id">{{ latest_received_message }}</p>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                            <div id="bottom-bar">
                                <button id="addcontact"><i class="fa fa-user-plus fa-fw" aria-hidden="true"></i> <span>Add contact</span></button>
                                <button id="settings"><i class="fa fa-cog fa-fw" aria-hidden="true"></i> <span>Settings</span></button>
                            </div>
                        </div>
                        <div class="content">
                            <div class="contact-profile">
                                <img src="http://emilcarlsson.se/assets/harveyspecter.png" alt="" />
                                <p>{{ activeUser }}</p>
                                <div class="social-media">
                                    <i class="fa fa-facebook" aria-hidden="true"></i>
                                    <i class="fa fa-twitter" aria-hidden="true"></i>
                                    <i class="fa fa-instagram" aria-hidden="true"></i>
                                </div>
                            </div>
                            <div class="messages">
                                <ul>
                                    <li v-for="message in messages" v-if="message.sender_id == auth.id" class="replies">

                                        <p v-if="message.body">{{ message.body }}</p>
                                        <img v-else :src="'storage/images/'+message.image" alt="" style="width: auto; border-radius: initial" />
                                        <div class="dropdown">
                                            <button class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown">
                                                <!--<i class="fa fa-ellipsis-v dropdown-toggle" data-toggle="dropdown"></i>--></button>
                                            <ul class="dropdown-menu">
                                                <li v-if="message.body" @click="editMessage(message.id, message.body)"><a href="JavaScript:Void(0)">Edit</a></li>
                                                <li><a href="JavaScript:Void(0)" @click="deleteMessage(message.id)">Delete</a></li>
                                            </ul>
                                        </div>
                                    </li>
                                    <li v-else="" class="sent">
                                        <img src="http://emilcarlsson.se/assets/mikeross.png" alt="" />
                                        <p v-if="message.body">{{ message.body }}</p>
                                        <img v-else :src="'storage/images/'+message.image" alt="" style="width: auto; border-radius: initial" />
                                    </li>
                                </ul>
                            </div>
                            <div class="message-input">
                                <div class="wrap">
                                    <form v-on:submit.prevent="saveForm()">
                                        <input type="text" v-model="body" placeholder="Write your message..." required />
                                        <i class="fa fa-paperclip attachment" aria-hidden="true" @click="getAttachment"></i>
                                        <input type="file" id="file" ref="file" v-on:change="onChangeFileUpload()" accept="image/*" style="display: none"/>
                                        <button class="submit"><i class="fa fa-paper-plane" aria-hidden="true"></i></button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data: function () {
            return {
                auth : window.auth,
                socket : null,
                active: [],
                messages: [],
                body: null,
                message_id: null,
                selected: null,
                sender_id: null,
                activeUser: null,
                //latest_sent_message: null,
                latest_received_message: null,
                users : []
            }
        },
        methods:{
            getAllUsers: function(){
                let _this = this;
                axios.get('users').then(function (response) {
                    _this.users = response.data;
                });
            },
            getUser: function(id, name){
                let _this = this;
                this.selected = id;
                this.activeUser = name;
                event.target.classList.toggle('active');
                axios.get('messages/' + id).then(function (response) {
                    _this.messages = response.data;
                    setTimeout(function(){ _this.$el.querySelector(".messages").scrollTop = _this.$el.querySelector(".messages").scrollHeight; }, 10);
                });
            },
            saveForm: function() {
                let _this = this;
                var body = this.body;
                if (this.message_id){
                    axios.post('messages/'+_this.message_id, { '_method':  'put', 'body': body})
                        .then(function (response) {
                            var objIndex = _this.messages.findIndex(x => x.id === _this.message_id);
                            _this.messages[objIndex].body = response.data.body;
                            _this.socket.emit('send-updated-message', {'id': _this.selected, 'message_id': _this.message_id, 'message': response.data});
                            _this.body = '';
                            _this.message_id = null;
                        })
                        .catch(function () {
                            alert("Could not update your message!");
                        });
                }else{
                    axios.post('messages', { 'receiver_id':  this.selected, 'body': body})
                        .then(function (response) {
                            _this.messages.push(response.data);
                            //_this.latest_sent_message = 'You: ' + response.data.body;
                            setTimeout(function(){ _this.$el.querySelector(".messages").scrollTop = _this.$el.querySelector(".messages").scrollHeight; }, 10);
                            _this.socket.emit('send-chat-message', {'id': _this.selected, 'sender_id': _this.auth.id, 'message': response.data});
                            _this.body = '';
                        })
                        .catch(function () {
                            alert("Could not send your message!");
                        });
                }
            },
            getAttachment: function () {
                $("input[type='file']").trigger('click');
            },
            onChangeFileUpload() {
                let _this = this;
                var file = this.$refs.file.files[0];
                if (file['type'] === 'image/jpg' || file['type'] === 'image/png' || file['type'] === 'image/jpeg' || file['type'] === 'image/tiff' || file['type'] === 'image/gif' || file['type'] === 'image/bmp' || file['type'] === 'image/ppm' || file['type'] === 'image/pgm' || file['type'] === 'image/pbm ' || file['type'] === 'image/pnm' || file['type'] === 'image/webp' || file['type'] === 'image/pjp'  || file['type'] === 'image/jfif' || file['type'] === 'image/pjpeg' || file['type'] === 'image/svgz' || file['type'] === 'image/svg' || file['type'] === 'image/ico' || file['type'] === 'image/xbm' || file['type'] === 'image/dib') {
                    if (file.size > 1048576) {
                        alert("File is too big! Your file should not exceed 1KB!");
                        return;
                    }
                    let formData = new FormData();
                    formData.append('image', this.$refs.file.files[0]);
                    formData.append('receiver_id', this.selected);

                    axios.post('messages/image',
                        formData,
                        {
                            headers: {
                                'Content-Type': 'multipart/form-data'
                            }
                        }
                    ).then(function (response) {
                        _this.messages.push(response.data);
                        setTimeout(function(){ _this.$el.querySelector(".messages").scrollTop = _this.$el.querySelector(".messages").scrollHeight; }, 10);
                        _this.socket.emit('send-chat-message', {
                            'id': _this.selected,
                            'sender_id': _this.auth.id,
                            'message': response.data
                        });
                    }).catch(function () {
                        console.log('FAILURE!!');
                    });
                }else {
                    alert("File must be image type!");
                }
            },
            editMessage: function (id, body) {
                this.message_id = id;
                this.body = body;
            },
            deleteMessage: function (id) {
                let _this = this;
                axios.post('messages/' + id, { '_method':  'DELETE'})
                    .then(function () {
                        _this.messages.splice(_this.messages.findIndex(x => x.id === id), 1);
                        _this.socket.emit('send-deleted-message', {'id': _this.selected, 'message_id': id});
                    })
                    .catch(function () {
                        alert("Could not delete your message!");
                    });
            }
        },
        mounted: function () {
            let _this = this;
            this.getAllUsers();
            if(this.socket == null){
                this.socket = io.connect('http://localhost:3000/');

                this.socket.on('received-message'+_this.auth.id, function (response) {
                    _this.messages.push(response.message);
                    _this.latest_received_message = response.message.body;
                    _this.sender_id = response.sender_id;
                    setTimeout(function(){ _this.$el.querySelector(".messages").scrollTop = _this.$el.querySelector(".messages").scrollHeight; }, 10);
                });
                this.socket.on('updated-message'+_this.auth.id, function (response) {
                    var objIndex = _this.messages.findIndex(x => x.id === response.message_id);
                    _this.messages[objIndex].body = response.message.body;
                });
                this.socket.on('deleted-message'+_this.auth.id, function (response) {
                    _this.messages.splice(_this.messages.findIndex(x => x.id === response.message_id), 1);
                });
            }

            this.socket.emit('new-user', _this.auth.id);

            this.socket.on('user-connected', user_ids => {
                _this.active = user_ids;
            });

            this.socket.on('user-disconnected', user_ids => {
                _this.active = user_ids;
            });
        }
    }
</script>
