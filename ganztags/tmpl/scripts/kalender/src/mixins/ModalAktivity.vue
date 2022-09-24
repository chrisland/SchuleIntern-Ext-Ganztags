<template>
  <div class="si-modal" v-if="open" v-on:click.self="handlerClose">

    <div class="si-modal-box">
      <button class="si-modal-btn-close" v-on:click="handlerClose"></button>
      <div class="si-modal-content">

        <div class="flex-row">
          <ul class="noListStyle flex-1">
            <li v-bind:key="j" v-for="(item, j) in activity">
              <button class="si-btn" v-on:click="handlerActivity(item)">{{ item.title }}</button>
            </li>
          </ul>

          <ul class="noListStyle flex-1">
            <li v-bind:key="j" v-for="(item, j) in leaders">
              <button class="si-btn" v-on:click="handlerLeader(item)">{{ item.user.name }}</button>
            </li>
          </ul>
        </div>

      </div>
    </div>

  </div>
</template>

<script>


//import Item from '../components/Item.vue'


export default {

  components: {},
  data() {
    return {
      open: false,
      callback: false,
      form: {
        user: false,
        activity: false
      }
    };
  },
  props: {
    item: Object,
    activity: Array,
    leaders: Array
  },
  created: function () {
    var that = this;
    EventBus.$on('modal-aktivity--open', data => {

      if (data.callback) {
        that.callback = data.callback;
      }

      that.open = true;
    });
    EventBus.$on('modal-aktivity--close', data => {
      that.open = false;
      //that.item = {};
    });
  },
  methods: {
    handlerClose: function () {
      EventBus.$emit('modal-aktivity--close');
    },
    handlerActivity: function (item) {
      this.form.activity = item;
      this.handlerSelect();
    },
    handlerLeader: function (item) {
      this.form.leader = item;
      this.handlerSelect();
    },
    handlerSelect: function () {

      if (this.form.activity && this.form.leader) {
        if (this.callback && typeof this.callback == 'function') {
          this.callback(this.form);
          this.form = {
            user: false,
            activity: false
          };

        }
      }


    }
  }


};
</script>

<style>

</style>