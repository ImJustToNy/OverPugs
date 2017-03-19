<template>
  <div class="ui modal">
    <i class="close icon"></i>
    <div class="header">
      Create new match
    </div>
    <div class="content">
      <div class="description">
        <form class="ui form" id="addForm" method="POST">
          <div class="two fields">
            <div class="field">
              <label>Discord Invitation Link</label>
              <input type="text" name="invitationLink" autocomplete="off" placeholder="https://discord.gg/overwatch">
            </div>
            <div class="field">
              <label>Accepted languages</label>
              <select class="ui fluid search dropdown" name="languages" multiple="">
                <option value="US">English</option>
                <option value="DE">German</option>
                <option value="PL">Polish</option>
              </select>
            </div>
          </div>
          <div class="two fields">
            <div class="field">
              <label>Minumum rank</label>
              <input type="number" id="minRank" name="minRank" v-bind:value="user.rank - 1000" placeholder="eg. 2000">
            </div>
            <div class="field">
              <label>Maximum rank</label>
              <input type="number" id="maxRank" name="maxRank" v-bind:value="user.rank + 1000" placeholder="eg. 3000">
            </div>
          </div>
          <div class="ui error message"></div>
        </form>
      </div>
    </div>
    <div class="actions">
      <button type="submit" class="ui positive right labeled icon button submit" form="addForm">
        Save
        <i class="checkmark icon"></i>
      </button>
    </div>
  </div>
</template>

<script>
  export default {
    computed: {
      user () {
        return this.$store.state.user
      }
    },

    mounted () {
      $('select.dropdown').dropdown({
        maxSelections: 3
      });

      $('.ui.form').form({
        onSuccess: function () {
          alert('OK');
        },

        fields: {
          invitationLink: {
            rules: [
              {
                type: 'regExp',
                value: /https?:\/\/discord\.gg\/[a-z0-9]{6,}/gi,
              }
            ]
          },
          languages: {
            rules: [
              {
                type: 'minCount[1]',
              }
            ]
          },
          minRank: {
            rules: [
              {
                type: 'empty',
              }
            ]
          },
          maxRank: {
            rules: [
              {
                type: 'empty',
              }
            ]
          }
        }
      });
    }
  }
</script>