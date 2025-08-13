<template>
    <textarea class="form-control"></textarea>
</template>
<script>
    export default{
        props : {
            model: {
                required: true
            },
            height: {
                type: String,
                default: '150'
            },
            placeholder: {
                type: String,
                default: 'Question Text'
            },
            upload_type: {
                type: String,
                default: 'question'
            }
        },
        mounted() {
            let config = {
                height: this.height,
                placeholder: this.placeholder
            };
            let vm = this;
            let upload_type = this.upload_type
            var ImageButton = function (context) {
              var ui = $.summernote.ui;
              // create button
              var button = ui.button({
                contents: 'Image',
                tooltip: 'Insert Image',
                click: function () {
                    if (upload_type=='question') {
                        $('input[name=question_image]').click()
                    }
                    if (upload_type=='answer') {
                        $('input[name=answer_image]').click()
                    }
                    if (upload_type=='relative') {
                        $('input[name=relative_image]').click()
                    }
                }
              });

              return button.render();
            }
            
            config.toolbar = [
                // [groupName, [list of button]]
                ['mybutton', ['InsertImage']],
                ['view', ['fullscreen']]
            ]
            config.buttons = {InsertImage: ImageButton }
            config.disableResizeEditor = true
            config.callbacks = {
                onInit: function () {
                    $(vm.$el).summernote("code", vm.model);
                },
                onChange: function () {
                    vm.$emit('update:model', $(vm.$el).summernote('code'));
                },
                onImageUpload: function(files) {
                    
                }
            };
            $(this.$el).summernote(config);
        }
    }
</script>

<style>
    .note-editable td{
        border: 1px solid #222;
    }
</style>