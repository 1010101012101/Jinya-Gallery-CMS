<template>
  <div :class="`is--${gallery.orientation}`" @wheel="scroll" class="jinya-gallery-designer" ref="designer">
    <jinya-loader :loading="loading" class="jinya-loader--designer"/>
    <jinya-message :message="message" :state="state" v-if="state"/>
    <jinya-gallery-designer-button @click="add(-1)" type="add" v-if="!loading"/>
    <template v-for="(position, index) in videos" v-if="!loading">
      <jinya-gallery-designer-item :key="`item-${position.position}-${position.video.slug}`"
                                   @wheel.native="scroll">
        <template>
          <jinya-gallery-designer-video :poster="position.video.poster" :src="position.video.video"
                                        :video-key="position.video.videoKey" @wheel.native="scroll"/>
          <jinya-gallery-designer-button @click="edit(position, index)" @wheel.native="scroll" type="edit"/>
          <jinya-gallery-designer-position-button :decrease="true" @click="move(position, index, index - 1)"
                                                  @wheel.native="scroll"
                                                  v-if="index > 0"/>
          <jinya-gallery-designer-position-button :increase="true" @click="move(position, index, index + 1)"
                                                  @wheel.native="scroll" v-if="index + 1 < videos.length"/>
        </template>
      </jinya-gallery-designer-item>
      <jinya-gallery-designer-button :key="`button-${position.position}-${position.video.slug}`" @click="add(index)"
                                     @wheel.native="scroll"
                                     type="add"/>
    </template>
    <jinya-gallery-designer-add-view @close="addModal.show = false" @picked="saveAdd" gallery-type="video"
                                     v-if="addModal.show"/>
    <jinya-gallery-designer-edit-view @close="editModal.show = false" @delete="deleteVideo" @picked="saveEdit"
                                      gallery-type="video" v-if="editModal.show"/>
  </div>
</template>

<script>
  import JinyaRequest from '@/framework/Ajax/JinyaRequest';
  import JinyaLoader from '@/framework/Markup/Waiting/Loader';
  import DOMUtils from '@/framework/Utils/DOMUtils';
  import Translator from '@/framework/i18n/Translator';
  import JinyaButton from '@/framework/Markup/Button';
  import JinyaGalleryDesignerPositionButton from '@/components/Art/Galleries/Designer/PositionButton';
  import JinyaGalleryDesignerButton from '@/components/Art/Galleries/Designer/Button';
  import JinyaGalleryDesignerItem from '@/components/Art/Galleries/Designer/Item';
  import JinyaModal from '@/framework/Markup/Modal/Modal';
  import JinyaGalleryDesignerAddView from '@/components/Art/Galleries/Designer/Add';
  import JinyaMessage from '@/framework/Markup/Validation/Message';
  import JinyaGalleryDesignerEditView from '@/components/Art/Galleries/Designer/Edit';
  import JinyaGalleryDesignerVideo from '@/components/Art/Galleries/Designer/Video';

  export default {
    components: {
      JinyaGalleryDesignerVideo,
      JinyaGalleryDesignerEditView,
      JinyaMessage,
      JinyaGalleryDesignerAddView,
      JinyaModal,
      JinyaGalleryDesignerItem,
      JinyaGalleryDesignerButton,
      JinyaGalleryDesignerPositionButton,
      JinyaButton,
      JinyaLoader,
    },
    name: 'designer',
    async mounted() {
      this.loading = true;
      try {
        const gallery = await JinyaRequest.get(`/api/gallery/video/${this.$route.params.slug}`);
        this.gallery = gallery.item;
        this.videos = await JinyaRequest.get(`/api/gallery/video/${this.$route.params.slug}/video`);
        DOMUtils.changeTitle(Translator.message('art.galleries.designer.title', this.gallery));
      } catch (error) {
        this.state = 'error';
        this.message = Translator.message('art.galleries.designer.loading_failed');
      }
      this.loading = false;
    },
    methods: {
      scroll($event) {
        if (!$event.deltaX && !this.addModel.show && !this.editModel.show) {
          this.$refs.designer.scrollBy({
            behavior: 'auto',
            left: $event.deltaY > 0 ? 100 : -100,
          });
        }
      },
      async move(videoPosition, oldPosition, newPosition) {
        if (oldPosition < newPosition) {
          this.videos.splice(newPosition + 1, 0, videoPosition);
          this.videos.splice(oldPosition, 1);
        } else {
          this.videos.splice(newPosition, 0, videoPosition);
          this.videos.splice(oldPosition + 1, 1);
        }
        await JinyaRequest.put(`/api/gallery/video/${this.gallery.slug}/video/${videoPosition.id}/${oldPosition}`, {
          position: newPosition,
        });
      },
      async saveAdd(video) {
        const id = await JinyaRequest.post(`/api/gallery/video/${this.gallery.slug}/video`, {
          position: this.currentPosition,
          video: video.slug,
          type: video.type,
        });

        this.videos.splice(this.currentPosition + 1, 0, {
          id,
          video,
        });

        this.addModal.show = false;
      },
      async saveEdit(video) {
        // eslint-disable-next-line max-len
        await JinyaRequest.put(`/api/gallery/video/${this.gallery.slug}/video/${this.videoPosition.id}/${this.currentPosition}`, {
          video: video.slug,
          type: video.type,
        });

        this.videos.splice(this.currentPosition, 1, {
          video,
          id: this.videoPosition.id,
        });

        this.editModal.show = false;
      },
      async deleteVideo() {
        await JinyaRequest.delete(`/api/gallery/video/${this.gallery.slug}/video/${this.videoPosition.id}`);

        this.videos.splice(this.currentPosition, 1);

        this.editModal.show = false;
      },
      add(position) {
        this.addModal.show = true;
        this.addModal.loading = true;
        this.currentPosition = position;
      },
      edit(videoPosition, position) {
        this.editModal.show = true;
        this.editModal.loading = true;
        this.currentPosition = position;
        this.videoPosition = videoPosition;
      },
    },
    data() {
      return {
        gallery: {
          name: '',
          orientation: '',
          background: '',
          slug: '',
        },
        state: '',
        message: '',
        videos: [],
        loading: false,
        addModal: {
          show: false,
          loading: false,
        },
        editModal: {
          show: false,
          loading: false,
        },
      };
    },
  };
</script>

<style lang="scss" scoped>
  .jinya-message--designer {
    margin-right: -12.5%;
    margin-left: -12.5%;
    width: 125%;
    padding-top: 1em;
  }
</style>

<style lang="scss">
  .jinya-gallery-designer {
    height: 100%;
    width: 100%;
    display: grid;
    grid-gap: 1em;

    &.is--horizontal {
      padding-bottom: 10em;
      grid-template-columns: repeat(auto-fill, minmax(10em, 100%));
      grid-auto-flow: column;
      padding-top: 1em;
      overflow-x: auto;
      margin-right: -12.5%;
      margin-left: -12.5%;
      width: 125%;
    }

    &.is--vertical {
      grid-template-rows: repeat(auto-fill, minmax(10em, 100%));
      padding-top: 1em;
    }
  }
</style>
