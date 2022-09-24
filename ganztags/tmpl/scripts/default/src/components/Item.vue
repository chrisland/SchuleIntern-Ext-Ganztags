<template>

  <div class="">

    <div class="flex-row">
      <div class="flex-1">
        <a href="#list" class="si-btn si-btn-light" v-on:click="handlerBack"><i class="fa fa-angle-left"></i> Zurück</a>
      </div>

    </div>

    <h2 class="padding-b-l">{{ item.vorname }} {{ item.nachname }}</h2>

    <User v-if="item" v-bind:data="item"></User>

    <div class="si-form">
      <ul>
        <li>
          <label>Anzahl der Tage</label>
          <input type="text" v-model="item.anz" v-on:change="handlerChange(item)">
        </li>
        <li v-bind:key="index" v-for="(day, index) in  showDays" v-if="day" >
          <span v-if="day">
            <label v-if="index == 'mo'">Montag</label>
            <label v-else-if="index == 'di'">Dienstag</label>
            <label v-else-if="index == 'mi'">Mittwoch</label>
            <label v-else-if="index == 'do'">Donnerstag</label>
            <label v-else-if="index == 'fr'">Freitag</label>
            <label v-else-if="index == 'sa'">Samstag</label>
            <label v-else-if="index == 'sa'">Sonntag</label>
            <div>
              <button v-if="item.days[index]" class="si-btn si-btn-toggle-on" v-on:click="handlerToggleDay(index,item)">
                <i class="fa fas fa-toggle-on"></i> An</button>
              <button v-else class="si-btn si-btn-toggle-off" v-on:click="handlerToggleDay(index,item)">
                <i class="fa fas fa-toggle-off"></i> Aus</button>

              <div v-if="item.days[index]" class="blockInline">
                <select v-model="item.days[index].group" v-on:change="handlerChangeDay(item)">
                  <option v-bind:key="index" v-for="(item, index) in  groups" :value="item.id">{{item.title}}</option>
                </select>

                <input v-model="item.days[index].info" v-on:change="handlerChangeDay(item)" type="text" placeholder="Info" class="">
              </div>
            </div>
          </span>
        </li>

        <li>
          <label>Info</label>
          <input type="text" v-model="item.info" v-on:change="handlerChange(item)">
        </li>


      </ul>
    </div>



  </div>

</template>


<script>

import User from './../mixins/User.vue'

export default {
  components: {
    User
  },
  name: 'List',
  props: {
    item: Object,
    acl: Object,
    groups: Array,
    showDays: Object
  },
  data() {
    return {
      deleteItem: false,
      sort: {
        column: false,
        order: true
      },
      tabActive: false,
      deleteTab: false
    }
  },
  computed: {

  },
  watch: {

  },
  created: function () {
  },
  mounted() {

  },
  methods: {

    handlerBack: function () {
      this.deleteItem = false;
      this.deleteTab = false;
      this.tabActive = false;
      //this.item = [];
      EventBus.$emit('tab--open', {
        tabOpen: 'list'
      });
    },

    handlerToggleDay: function (day, item) {


      if (item.days[day]) {
        item.days[day] = false;
      } else {
        item.days[day] = {group: 0, info: '' };
      }

      EventBus.$emit('item--change', {
        item: item,
        callback: function (data) {
          item.days = data.days;
        }
      });

    },
    handlerChangeDay: function (item) {


      EventBus.$emit('item--change', {
        item: item,
        callback: function (data) {
          item.days = data.days;
        }
      });

    },
    handlerChange: function (item) {


      EventBus.$emit('item--change', {
        item: item,
        callback: function (data) {
          //item.days = data.days;
        }
      });

    }
  }
}
</script>
