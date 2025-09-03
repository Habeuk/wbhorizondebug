<script setup>
//import { computed, onMounted, onBeforeUnmount, ref } from "vue";
import requestAxios from "./requestAxios.js";
import entities from "./entities.js";
const basiqueCreate = (index) => {
  const paragraph = entities()[index];
  requestAxios.post("/apivuejs/save-entity/paragraph", { entity: paragraph });
};
</script>

<template>
  <div class="container space-padding">
    <span class="btn btn-outline-wbu-background border-radius-10 me-4" @click="basiqueCreate('paragraph1')"> paragraph1 </span>
    <span class="btn btn-outline-wbu-background border-radius-10 me-4" @click="basiqueCreate('paragraph2')"> paragraph2 </span>
    <span class="btn btn-outline-wbu-background border-radius-10 me-4" @click="basiqueCreate('paragraph3')"> paragraph3 </span>
    <span class="btn btn-outline-wbu-background border-radius-10 me-4" @click="basiqueCreate('paragraph4')"> paragraph4 </span>
  </div>
</template>
