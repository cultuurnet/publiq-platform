<template>
  <panelItem :field="field">
    <template #value>
      <a class="link-default" :href="field.baseUrl + field.value" target="_blank">{{ field.value }}</a>
    </template>
  </panelItem>
</template>

<script>
export default {
  props: ['resource', 'resourceName', 'resourceId', 'field'],
}
</script>
