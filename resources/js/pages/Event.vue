<template>
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Events</h1>
                        <p class="text-gray-600 mt-2">Discover and register for upcoming events</p>
                    </div>
                    <button @click="showCreateModal = true"
                        class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition duration-200 font-medium">
                        Create Event
                    </button>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-blue-100 rounded-lg">
                            <CalendarIcon class="h-6 w-6 text-blue-600" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Total Events</p>
                            <p class="text-2xl font-bold text-gray-900">{{ stats.total_events }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-green-100 rounded-lg">
                            <UsersIcon class="h-6 w-6 text-green-600" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Total Registrations</p>
                            <p class="text-2xl font-bold text-gray-900">{{ stats.total_registrations }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-orange-100 rounded-lg">
                            <ClockIcon class="h-6 w-6 text-orange-600" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Upcoming Events</p>
                            <p class="text-2xl font-bold text-gray-900">{{ stats.upcoming_events }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-purple-100 rounded-lg">
                            <ChartBarIcon class="h-6 w-6 text-purple-600" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Avg Attendance</p>
                            <p class="text-2xl font-bold text-gray-900">{{ stats.average_attendance }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-lg shadow mb-6 p-4">
                <div class="flex flex-col sm:flex-row gap-4">
                    <div class="flex-1">
                        <input v-model="filters.search" type="text" placeholder="Search events..."
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                    </div>
                    <select v-model="filters.status"
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="All">All Events</option>
                        <option value="Upcoming">Upcoming</option>
                        <option value="Ongoing">Ongoing</option>
                        <option value="Completed">Completed</option>
                    </select>
                    <button @click="loadEvents"
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200">
                        Apply
                    </button>
                </div>
            </div>

            <!-- Events Grid -->
            <div v-if="loading" class="flex justify-center py-12">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
            </div>

            <div v-else-if="events.length === 0" class="text-center py-12">
                <CalendarIcon class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-medium text-gray-900">No events found</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by creating a new event.</p>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <EventDetail v-for="event in events" :key="event.id" :event="event" @register="handleRegistration"
                    @cancel="handleCancellation" @view-registrants="viewRegistrants" @edit="editEvent"
                    @delete="deleteEvent" />
            </div>

            <!-- Create Event Modal -->
            <RegistrationModal :show="showCreateModal" :event="editingEvent" @close="closeModal"
                @saved="handleEventSaved" />

            <!-- Registrants Modal -->
            <RegistrantsList :show="showRegistrantsModal" :event="selectedEvent" @close="showRegistrantsModal = false"
                @registrant-removed="handleRegistrantRemoved" />
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import {
    CalendarIcon,
    UsersIcon,
    ClockIcon,
    ChartBarIcon
} from '@heroicons/vue/24/outline'
import EventDetail from '../components/EventDetail.vue'
import RegistrantsList from '../components/RegistrantsList.vue'
import RegistrationModal from '../components/RegistrationModal.vue'

// Reactive data
const events = ref([])
const stats = ref({
    total_events: 0,
    total_registrations: 0,
    upcoming_events: 0,
    average_attendance: 0
})
const loading = ref(false)
const showCreateModal = ref(false)
const showRegistrantsModal = ref(false)
const editingEvent = ref(null)
const selectedEvent = ref(null)

const filters = ref({
    status: 'All',
    search: ''
})

// Methods
const loadEvents = async () => {
    loading.value = true
    try {
        const params = new URLSearchParams(filters.value)
        const response = await fetch(`/events/data?${params}`)
        const data = await response.json()

        events.value = data.events
        stats.value = data.stats
    } catch (error) {
        console.error('Failed to load events:', error)
        alert('Failed to load events')
    } finally {
        loading.value = false
    }
}

const handleRegistration = async (event) => {
    try {
        const response = await fetch(`/events/${event.id}/register`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })

        const data = await response.json()

        if (data.success) {
            await loadEvents()
            alert(data.message)
        } else {
            alert(data.message)
        }
    } catch (error) {
        console.error('Registration failed:', error)
        alert('Registration failed')
    }
}

const handleCancellation = async (event) => {
    if (!confirm('Are you sure you want to cancel your registration?')) {
        return
    }

    try {
        const response = await fetch(`/events/${event.id}/register`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })

        const data = await response.json()

        if (data.success) {
            await loadEvents()
            alert(data.message)
        } else {
            alert(data.message)
        }
    } catch (error) {
        console.error('Cancellation failed:', error)
        alert('Cancellation failed')
    }
}

const viewRegistrants = (event) => {
    selectedEvent.value = event
    showRegistrantsModal.value = true
}

const editEvent = (event) => {
    editingEvent.value = event
    showCreateModal.value = true
}

const deleteEvent = async (event) => {
    if (!confirm('Are you sure you want to delete this event? This action cannot be undone.')) {
        return
    }

    try {
        const response = await fetch(`/events/${event.id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })

        if (response.ok) {
            await loadEvents()
            alert('Event deleted successfully')
        } else {
            alert('Failed to delete event')
        }
    } catch (error) {
        console.error('Delete failed:', error)
        alert('Delete failed')
    }
}

const closeModal = () => {
    showCreateModal.value = false
    editingEvent.value = null
}

const handleEventSaved = () => {
    closeModal()
    loadEvents()
}

const handleRegistrantRemoved = () => {
    loadEvents()
}

// Lifecycle
onMounted(() => {
    loadEvents()
})
</script>