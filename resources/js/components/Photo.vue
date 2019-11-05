<template>
  <div class="photo">
    <figure class="photo__wrapper">
      <img
        class="photo__image"
        :class="imageClass"
        :src="item.url"
        :alt="`Photo by ${item.owner.name}`"
        @load="setAspectRatio"
        ref="image"
      />
    </figure>
    <router-link
      class="photo__overlay"
      :to="`/photos/${item.id}`"
      :title="`View the photo by ${item.owner.name}`"
    >
      <div class="photo__controls">
        <!-- いいね -->
        <button
          class="photo__action photo__action--like"
          :class="{ 'photo__action--liked': item.liked_by_user }"
          title="Like photo"
          @click.prevent="like"
        >
          <i class="icon ion-md-heart"></i>
          {{ item.likes_count }}
        </button>
        <!-- download -->
        <a
          :href="`/photos/${item.id}/download`"
          @click.stop
          class="photo__action"
          title="Download photo"
        >
          <i class="icon ion-md-arrow-round-down"></i>
        </a>
      </div>
      <!-- 投稿者名 -->
      <div class="photo__username">{{ item.owner.name }}</div>
    </router-link>
  </div>
</template>

<script>
export default {
  props: {
    item: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      landscape: false,
      portrait: false
    };
  },
  computed: {
    imageClass() {
      return {
        "photo__image--landscape": this.landscape,
        "photo__image--portrait": this.portrait
      };
    }
  },
  methods: {
    setAspectRatio() {
      if (!this.$refs.image) {
        return false;
      }
      const height = this.$refs.image.clientHeight;
      const width = this.$refs.image.clientWidth;

      this.landscape = height / width <= 0.75;
      this.portrait = !this.landscape;
    },
    like() {
      this.$emit("like", {
        id: this.item.id,
        liked: this.item.liked_by_user
      });
    },
    goToTopPage() {
      this.$router.push("/");
    }
  },
  watch: {
    $route() {
      this.landscape = false;
      this.portrait = false;
    }
  }
};
</script>