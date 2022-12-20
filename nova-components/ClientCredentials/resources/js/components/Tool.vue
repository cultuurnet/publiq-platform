<template>
    <div>
        <h1 class="text-90 font-normal text-2xl">{{title}}</h1>
        <table class="w-full bg-white dark:bg-gray-800 shadow mt-3 py-2 px-6">
            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                    <th class="text-left px-2 whitespace-nowrap uppercase text-gray-500 text-xxs tracking-wide py-2">
                        <span>{{ environmentLabel }}</span>
                    </th>
                    <th class="text-left px-2 whitespace-nowrap uppercase text-gray-500 text-xxs tracking-wide py-2">
                        <span>{{ idLabel }}</span>
                    </th>
                    <th class="text-left px-2 whitespace-nowrap uppercase text-gray-500 text-xxs tracking-wide py-2">
                        <span>{{ secretLabel }}</span>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="set in sets" class="group">
                    <td class="px-2 py-2 border-t border-gray-100 dark:border-gray-700 whitespace-nowrap dark:bg-gray-800 group-hover:bg-gray-50 dark:group-hover:bg-gray-900">
                        <div class="text-left">
                            <span class="text-90 whitespace-nowrap">{{ set.env }}</span>
                        </div>
                    </td>
                    <td class="px-2 py-2 border-t border-gray-100 dark:border-gray-700 whitespace-nowrap dark:bg-gray-800 group-hover:bg-gray-50 dark:group-hover:bg-gray-900">
                        <div class="text-left">
                            <span class="text-90 whitespace-nowrap">{{ set.id }}</span>
                        </div>
                    </td>
                    <td class="px-2 py-2 border-t border-gray-100 dark:border-gray-700 whitespace-nowrap dark:bg-gray-800 group-hover:bg-gray-50 dark:group-hover:bg-gray-900">
                        <div class="text-left">
                            <span class="text-90 whitespace-nowrap">{{ set.secret }}</span>
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
        idLabel() {
            return this.panel.fields[0]['idLabel']
        },
        secretLabel() {
            return this.panel.fields[0]['secretLabel']
        },
        environmentLabel() {
            return this.panel.fields[0]['environmentLabel']
        },
        sets() {
            return this.panel.fields[0]['sets']
        },
    },
}
</script>
