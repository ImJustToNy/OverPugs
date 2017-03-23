<template>
  <div class="ui centered leaderboard test ad" data-text="" v-if="adsEnabled">
    <ins class="adsbygoogle"
       style="display:inline-block;width:728px;height:90px"
       data-ad-client="ca-pub-1871612708734823"
       v-bind:data-ad-slot="adslot"></ins>
  </div>
</template>

<script>
  export default {
    props: ['adslot'],

    data () {
      return {
        adsEnabled: false,
      }
    }
  }
</script>