<template>
  <video :poster="poster" :src="src" class="jinya-gallery-designer__video" v-if="poster && src"></video>
  <iframe :src="`https://www.youtube-nocookie.com/embed/${videoKey}`" class="jinya-gallery-designer__iframe"
          v-else-if="videoKey"></iframe>
</template>

<script>
  export default {
    name: 'jinya-gallery-designer-video',
    props: {
      src: {
        type: String,
      },
      poster: {
        type: String,
      },
      videoKey: {
        type: String,
      },
    },
  };
</script>

<style lang="scss">
  .jinya-gallery-designer__video {
    .is--vertical & {
      height: auto;
      width: 100%;
      grid-row: 2;
      grid-column: 1;
    }

    .is--horizontal & {
      height: 40em;
      width: auto;
      grid-column: 1;
      grid-row: 1;
    }
  }

  .jinya-gallery-designer__iframe {
    .is--vertical & {
      height: auto;
      width: 100%;
      grid-row: 2;
      grid-column: 1;
    }

    .is--horizontal & {
      height: 40em;
      width: 70em;
      grid-column: 1;
      grid-row: 1;
    }
  }
</style>
