<template>
  <div class="ui fixed menu">
    <div class="ui container">
      <a href="#" class="header item">
        <img class="logo" src="/images/logo.png" width="128">
        OverSearch
      </a>
      <a href="/" class="item">Home</a>
      <div class="right green menu">
        <div class="item" v-if="user">
          <img v-bind:src="user.avatar_url" alt="User's avatar" class="logo"> {{ user.tag | friendlyTag }}
        </div>
        <div class="item" v-if="user">
          <img v-bind:src="user.rank | imageRank" alt="User's rank"> {{ user.rank }}
        </div>
        <a class="item">
          <div class="ui green button" v-on:click="openAddModal"><i class="icon plus"></i> Add new game</div>
        </a>
      </div>
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

  methods: {
    openAddModal: function () {
      if (!this.user) {
        window.location.href = '/login';
      } else {
        $('.ui.modal').modal({
          onApprove: function () {
            return false;
          }
        }).modal('show');
      }
    }
  }
}
</script>