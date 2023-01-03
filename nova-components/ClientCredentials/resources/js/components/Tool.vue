<template>
    <div>
        <h1 class="text-90 font-normal text-2xl">{{title}}</h1>
        <table class="w-full bg-white dark:bg-gray-800 shadow mt-3 py-2 px-6">
            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                    <th v-for="header in headers" class="text-left px-2 whitespace-nowrap uppercase text-gray-500 text-xxs tracking-wide py-2">
                        <span>{{ header }}</span>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="row in rows" class="group">
                    <td v-for="cell in row" class="px-2 py-2 border-t border-gray-100 dark:border-gray-700 whitespace-nowrap dark:bg-gray-800 group-hover:bg-gray-50 dark:group-hover:bg-gray-900">
                        <div class="text-left">
                            <span class="text-90 whitespace-nowrap">{{ cell }}</span>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
export default {
    props: ['resourceName', 'resourceId', 'panel'],

    mounted() {},

    computed: {
        title () {
            return this.panel.fields[0]['title']
        },
        headers() {
            return this.panel.fields[0]['headers']
        },
        rows() {
            return this.panel.fields[0]['rows']
        },
    },
}
</script>
