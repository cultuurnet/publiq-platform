<template>
  <panelItem :field="field">
    <template #value>
      <div v-if="field.value">
        <a class="link-default" :href="field.baseUrl + field.value" target="_blank">{{ field.value }}</a>
      </div>
      <p v-else>&mdash;</p>
    </template>
  </panelItem>
</template>

<script>
export default {
  props: ['resource', 'resourceName', 'resourceId', 'field'],
}
</script>
