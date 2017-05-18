<template>
  <div class="ui modal askedMatch">
    <i class="close icon"></i>
    <div class="header">
      Match details
    </div>
    <div class="content">
      <div class="description">
        <adsense
                ad-client="ca-pub-1871612708734823"
                ad-slot="6825934798"
                ad-style="display: block"
                ad-format="auto">
        </adsense>

        <table class="ui table center aligned" v-if="match">
          <tbody>
            <game v-bind:match="match"></game>
          </tbody>
        </table>

        <h2 class="ui center aligned icon header disabled" v-else>
          <i class="clock icon"></i>
          {{ error }}
        </h2>
      </div>
    </div>
  </div>
</template>

<script>
  export default {
    data () {
      return {
        match: false,
        error: false
      }
    },

    beforeMount () {
      if (OverPugs.askedMatch == '0') {
        this.error = 'No lobby like that found or it expired';
      } else if (OverPugs.askedMatch != null) {
        this.match = OverPugs.askedMatch;
      }
    },

    mounted () {
      if (this.match != false || this.error != false) {
        $('.askedMatch').modal('show');
      }
    }
  }
</script>