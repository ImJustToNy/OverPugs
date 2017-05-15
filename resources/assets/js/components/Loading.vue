<template>

  <div class="ui active dimmer" v-if="isLoading">
    <div class="ui text loader">
      Loading...
    </div>
  </div>

</template>

<script>
  export default {
    computed: {
      isLoading () {
        return this.$store.state.loading
      }
    }
  }
</script>