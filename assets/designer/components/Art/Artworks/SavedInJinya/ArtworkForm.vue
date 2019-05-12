<template>
  <jinya-editor>
    <jinya-message :message="message" :state="state" v-if="state">
      <jinya-message-action-bar class="jinya-message__action-bar" v-if="state === 'error' && isStatic">
        <jinya-button :is-danger="true" label="art.artworks.artwork_form.back"
                      to="Art.Artworks.SavedInJinya.Overview"/>
        <jinya-button :is-secondary="true" :query="{keyword: $route.params.slug}"
                      label="art.artworks.artwork_form.search" to="Art.Artworks.SavedInJinya.Overview"/>
      </jinya-message-action-bar>
    </jinya-message>
    <jinya-form :cancel-label="cancelLabel" :enable="enable" :save-label="saveLabel" @back="back"
                @submit="save" class="jinya-form--artwork" v-if="!(hideOnError && state === 'error')">
      <jinya-editor-pane>
        <jinya-editor-preview-image :src="artwork.picture"/>
      </jinya-editor-pane>
      <jinya-editor-pane>
        <jinya-input :enable="enable" :is-static="isStatic" :required="true"
                     :validation-message="'art.artworks.artwork_form.name.empty'|jvalidator" @change="nameChanged"
                     label="art.artworks.artwork_form.name" v-model="artwork.name"/>
        <jinya-input :enable="enable" :is-static="isStatic" :required="true"
                     :validation-message="'art.artworks.artwork_form.slug.empty'|jvalidator" @change="slugChanged"
                     label="art.artworks.artwork_form.slug" v-model="artwork.slug"/>
        <jinya-file-input :enable="enable" :required="true"
                          :validation-message="'art.artworks.artwork_form.artwork.empty'|jvalidator"
                          @picked="picturePicked"
                          accept="image/*" label="art.artworks.artwork_form.artwork" v-if="!isStatic"/>
        <jinya-textarea :enable="enable" :is-static="isStatic" label="art.artworks.artwork_form.description"
                        v-model="artwork.description"/>
      </jinya-editor-pane>
      <template slot="buttons">
        <slot name="buttons"/>
      </template>
    </jinya-form>
  </jinya-editor>
</template>

<script>
  import JinyaForm from '@/framework/Markup/Form/Form';
  import JinyaInput from '@/framework/Markup/Form/Input';
  import JinyaButton from '@/framework/Markup/Button';
  import JinyaFileInput from '@/framework/Markup/Form/FileInput';
  import FileUtils from '@/framework/IO/FileUtils';
  import JinyaTextarea from '@/framework/Markup/Form/Textarea';
  import slugify from 'slugify';
  import Routes from '@/router/Routes';
  import JinyaMessage from '@/framework/Markup/Validation/Message';
  import JinyaMessageActionBar from '@/framework/Markup/Validation/MessageActionBar';
  import JinyaEditor from '@/framework/Markup/Form/Editor';
  import JinyaEditorPreviewImage from '@/framework/Markup/Form/EditorPreviewImage';
  import JinyaEditorPane from '@/framework/Markup/Form/EditorPane';

  export default {
    components: {
      JinyaTextarea,
      JinyaFileInput,
      JinyaInput,
      JinyaEditorPreviewImage,
      JinyaEditorPane,
      JinyaForm,
      JinyaButton,
      JinyaMessageActionBar,
      JinyaMessage,
      JinyaEditor,
    },
    name: 'jinya-artwork-form',
    props: {
      message: {
        type: String,
        default() {
          return '';
        },
      },
      state: {
        type: String,
        default() {
          return '';
        },
      },
      isStatic: {
        type: Boolean,
        default() {
          return false;
        },
      },
      enable: {
        type: Boolean,
        default() {
          return true;
        },
      },
      hideOnError: {
        type: Boolean,
        default() {
          return false;
        },
      },
      saveLabel: {
        type: String,
        default() {
          return 'art.artworks.artwork_form.save';
        },
      },
      cancelLabel: {
        type: String,
        default() {
          return 'art.artworks.artwork_form.back';
        },
      },
      artwork: {
        type: Object,
        default() {
          return {
            picture: '',
            name: '',
            slug: '',
            description: '',
          };
        },
      },
      slugifyEnabled: {
        type: Boolean,
        default() {
          return true;
        },
      },
    },
    methods: {
      back() {
        this.$router.push(Routes.Art.Artworks.SavedInJinya.Overview);
      },
      async picturePicked(files) {
        const file = files.item(0);

        this.artwork.picture = await FileUtils.getAsDataUrl(file);
        this.artwork.uploadedFile = file;
      },
      nameChanged(value) {
        if (this.slugifyEnabled) {
          this.artwork.slug = slugify(value);
        }
      },
      slugChanged(value) {
        this.slugifyEnabled = false;
        this.artwork.slug = slugify(value);
      },
      save() {
        const artwork = {
          name: this.artwork.name,
          slug: this.artwork.slug,
          picture: this.artwork.uploadedFile,
          description: this.artwork.description,
        };

        this.$emit('save', artwork);
      },
    },
  };
</script>