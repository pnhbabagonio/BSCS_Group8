<script setup lang="ts">
import { ref, computed } from "vue"
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import EventListTab from '@/components/EventManagement/EventListTab.vue'
import AddEventTab from '@/components/EventManagement/AddEventTab.vue'
import EventOverviewTab from '@/components/EventManagement/EventOverviewTab.vue'
import ManualRegistrationTab from "@/components/EventManagement/ManualRegistrationTab.vue"
// Props
interface Event {
    id: number
    title: string
    description: string
    date: string
    time: string
    location: string
    capacity: number
    registered: number
    status: 'upcoming' | 'ongoing' | 'completed' | 'cancelled'
    category: string
    is_full: boolean
}

const props = defineProps<{
    events: Event[]
    filters: {
        search: string
    }
}>()

const activeTab = ref("overview")

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Event Management', href: '/event-management' },
]

const tabs = [
    { key: "overview", label: "Event Overview", icon: "chart" },
    { key: "events", label: "Event List", icon: "calendar" },
    { key: "add", label: "Add Event", icon: "plus" },
    { key: "registration", label: "Manual Registration", icon: "users" },
]

// Computed
const eventStats = computed(() => {
    return {
        total: props.events.length,
        upcoming: props.events.filter(e => e.status === 'upcoming').length,
        ongoing: props.events.filter(e => e.status === 'ongoing').length,
        completed: props.events.filter(e => e.status === 'completed').length,
        cancelled: props.events.filter(e => e.status === 'cancelled').length,
        full: props.events.filter(e => e.is_full).length,
    }
})

// Methods
const switchToEventList = () => {
    activeTab.value = "events"
}

const switchToAddEvent = () => {
    activeTab.value = "add"
}

const switchToManualRegistration = () => {
    activeTab.value = "registration"
}

// Tab icons
const getTabIcon = (iconType: string) => {
    const icons = {
        chart: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>`,
        calendar: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>`,
        plus: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>`,
        users: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>`
    }
    return (icons as Record<string, string>)[iconType] ?? icons.chart
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 text-gray-200">
            <!-- Tabs Navigation -->
            <div class="flex space-x-6 border-b border-gray-800 mb-6">
                <button v-for="tab in tabs" :key="tab.key" @click="activeTab = tab.key"
                    class="pb-2 flex items-center gap-2 transition-all duration-200" :class="[

                        activeTab === tab.key
                            ? 'border-b-2 border-black text-black'
                            : 'text-gray-400 hover:text-gray-200'
                    ]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <g v-html="getTabIcon(tab.icon)"></g>
                    </svg>
                    {{ tab.label }}
                </button>
            </div>

            <!-- Tab Content -->
            <div class="min-h-96">
                <EventOverviewTab v-if="activeTab === 'overview'" :events="events" :stats="eventStats"
                    @switch-to-events="switchToEventList" @switch-to-add="switchToAddEvent" />

                <EventListTab v-else-if="activeTab === 'events'" :events="events" :filters="filters" />

                <AddEventTab v-else-if="activeTab === 'add'" @event-created="switchToEventList" />
                <ManualRegistrationTab v-else-if="activeTab === 'registration'" :events="events" />
            </div>
        </div>
    </AppLayout>
</template>