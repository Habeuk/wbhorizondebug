import { createApp } from "vue";
import GenerateEntities from "./GenerateEntities.vue";
const el = document.getElementById("app-enerate-entities");
if (el) {
  const app = createApp(GenerateEntities, {});
  app.mount(el);
}
