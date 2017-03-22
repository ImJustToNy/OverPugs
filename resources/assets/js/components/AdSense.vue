<template>
  <div class="ui center aligned segment" v-if="production">
    <ins class="adsbygoogle"
       style="display:inline-block;width:728px;height:90px"
       data-ad-client="ca-pub-1871612708734823"
       v-bind:data-ad-slot="adslot"></ins>
  </div>
</template>

<script>
  export default {
    props: ['adslot'],

    computed: {
      production () {
        return this.$store.state.production
      }
    }
  }
</script>