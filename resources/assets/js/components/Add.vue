<template>
  <div class="ui modal" v-if="user && profile">
    <i class="close icon"></i>
    <div class="header">
      Create new match
    </div>
    <div class="content">
      <div class="description">
        <form class="ui form" id="addForm" method="POST" v-on:submit.prevent>
          <div class="field">
            <label>Type</label>
            <select class="ui fluid dropdown" name="type" v-model="type">
              <option value="comp"><a class="ui mini red label">Competive</a></option>
              <option value="qp"><a class="ui mini green label">Quick Play</a></option>
              <option value="custom"><a class="ui mini blue label">Custom Games</a></option>
              <option value="brawl"><a class="ui mini yellow label">Brawl</a></option>
            </select>
          </div>
          <div class="two fields">
            <div class="field">
              <label>Discord Invitation Link</label>
              <input type="text" name="invitationLink" autocomplete="off" placeholder="Leave blank if you want to play without discord" value="https://discord.gg/uniqueID">
            </div>
            <div class="field">
              <label>Region</label>
              <select class="ui fluid dropdown" name="region">
                <option value="us"><i class="flag us"></i> North America</option>
                <option value="eu"><i class="flag eu"></i> Europe</option>
                <option value="kr"><i class="flag kr"></i> Korea</option>
              </select>
            </div>
          </div>
          <div class="two fields" v-show="type == 'comp'">
            <div class="field">
              <label>Minumum rank</label>
              <input type="number" id="minRank" name="minRank" v-bind:value="minRank" placeholder="eg. 2000">
            </div>
            <div class="field">
              <label>Maximum rank</label>
              <input type="number" id="maxRank" name="maxRank" v-bind:value="maxRank" placeholder="eg. 3000">
            </div>
          </div>
          <div class="field" v-show="type != 'comp'">
            <label>Description</label>
            <input type="text" name="description" autocomplete="off" placeholder="Eg. Mercy Hunt">
          </div>
          <div class="two fields">
            <div class="field">
              <label>How many people do you need?</label>
              <select class="ui fluid dropdown" name="howMuch">
                <option v-bind:value="loop" v-for="loop in 5">
                  <i class="icon user" v-for="nextLoop in loop"></i>
                  <i class="icon user outline" v-for="lastLoop in 5 - loop"></i>
                  {{ loop | pluralize('player') }}
                </option>
              </select>
            </div>
            <div class="field">
              <label>Accepted languages</label>
              <select class="ui fluid search dropdown" name="languages" multiple="">
                <option value="us" selected><i class="flag us"></i> English</option>
                <option value="de"><i class="flag de"></i> German</option>
                <option value="pl"><i class="flag pl"></i> Polish</option>
              </select>
            </div>
          </div>
          <div class="ui error message"></div>
        </form>
      </div>
    </div>
    <div class="actions">
      <div class="ui red message" v-if="error" v-text="error"></div>
      <button type="submit" class="ui positive right labeled icon button submit" v-bind:class="{ loading: formSubmitting }" v-bind:disabled="formSubmitting" form="addForm">
        Add
        <i class="checkmark icon"></i>
      </button>
    </div>
  </div>
</template>

<script>
  export default {
    data () {
      return {
        formSubmitting: false,
        error: '',
        type: 'comp'
      }
    },

    computed: {
      user () {
        return this.$store.state.user
      },

      profile () {
        return this.$store.state.profile
      },

      region () {
        return this.$store.state.region
      },

      minRank () {
        if (this.user) {
          var rank = this.profile.rank - 1000;

          if (rank < 1) {
            return 1;
          }

          return rank;
        } else {
          return null;
        }
      },

      maxRank () {
        if (this.user) {
          var rank = this.profile.rank + 1000;

          if (rank > 5000) {
            rank -= rank - 5000;
          }

          return rank;
        } else {
          return null;
        }
      }
    },

    mounted () {
      var $this = this;

      $('select.dropdown').dropdown({
        maxSelections: 3
      });

      $('.ui.form').form({
        onSuccess: function (event, fields) {
          $this.formSubmitting = true;

          $this.$http.post('match/add', fields)
            .then(response => {
              $this.formSubmitting = false
              $this.error = false
              
              $this.$store.dispatch('newMatch', response.body.match)
              $this.$store.dispatch('setServerTime', response.body.serverTime)

              $('.ui.modal').modal('hide')
            }, response => {
              $this.formSubmitting = false
              if (response.body.error) {
                $this.error = response.body.error
              } else {
                $this.error = response.body[Object.keys(response.body)[0]];
              }
            });
        },

        fields: {
          languages: ['minCount[1]', 'maxCount[3]'],
          description: 'maxLength[25]'
        }
      });
    }
  }
</script>