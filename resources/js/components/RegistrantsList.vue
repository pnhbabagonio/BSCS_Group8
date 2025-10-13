<template>
    <TransitionRoot as="template" :show="show">
        <Dialog as="div" class="relative z-10" @close="$emit('close')">
            <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100"
                leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
            </TransitionChild>

            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <TransitionChild as="template" enter="ease-out duration-300"
                        enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200"
                        leave-from="opacity-100 translate-y-0 sm:scale-100"
                        leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                        <DialogPanel
                            class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-4xl sm:p-6">
                            <!-- Header -->
                            <div class="mb-6">
                                <div class="flex items-center justify-between">
                                    <DialogTitle as="h3" class="text-lg font-semibold leading-6 text-gray-900">
                                        Registrants for {{ event.title }}
                                    </DialogTitle>
                                    <button @click="$emit('close')"
                                        class="text-gray-400 hover:text-gray-600 transition duration-200">
                                        <XMarkIcon class="h-6 w-6" />
                                    </button>
                                </div>
                                <p class="mt-1 text-sm text-gray-500">
                                    {{ event.registered_count }} registered, {{ event.waitlisted_count }} waitlisted
                                </p>
                            </div>

                            <!-- Stats -->
                            <div class="grid grid-cols-4 gap-4 mb-6">
                                <div class="bg-blue-50 p-4 rounded-lg">
                                    <p class="text-sm font-medium text-blue-800">Capacity</p>
                                    <p class="text-2xl font-bold text-blue-900">{{ event.max_capacity }}</p>
                                </div>
                                <div class="bg-green-50 p-4 rounded-lg">
                                    <p class="text-sm font-medium text-green-800">Registered</p>
                                    <p class="text-2xl font-bold text-green-900">{{ registrants.length }}</p>
                                </div>
                                <div class="bg-orange-50 p-4 rounded-lg">
                                    <p class="text-sm font-medium text-orange-800">Waitlisted</p>
                                    <p class="text-2xl font-bold text-orange-900">{{ waitlisted.length }}</p>
                                </div>
                                <div class="bg-purple-50 p-4 rounded-lg">
                                    <p class="text-sm font-medium text-purple-800">Available</p>
                                    <p class="text-2xl font-bold text-purple-900">{{ event.available_spots }}</p>
                                </div>
                            </div>

                            <!-- Tabs -->
                            <div class="border-b border-gray-200 mb-6">
                                <nav class="-mb-px flex space-x-8">
                                    <button v-for="tab in tabs" :key="tab.name" @click="activeTab = tab.name" :class="[
                                        'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm',
                                        activeTab === tab.name
                                            ? 'border-blue-500 text-blue-600'
                                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                    ]">
                                        {{ tab.name }} ({{ tab.count }})
                                    </button>
                                </nav>
                            </div>

                            <!-- Loading -->
                            <div v-if="loading" class="flex justify-center py-8">
                                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                            </div>

                            <!-- Registrants Table -->
                            <div v-else class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 rounded-lg">
                                <table class="min-w-full divide-y divide-gray-300">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                User
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Program
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Registered At
                                            </th>
                                            <th v-if="activeTab === 'Waitlisted'" scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Position
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="registrant in currentRegistrants" :key="registrant.id">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div
                                                        class="flex-shrink-0 h-10 w-10 bg-gray-300 rounded-full flex items-center justify-center">
                                                        <span class="text-sm font-medium text-gray-700">
                                                            {{ getInitials(registrant.name) }}
                                                        </span>
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900">{{
                                                            registrant.name }}</div>
                                                        <div class="text-sm text-gray-500">{{ registrant.email }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ registrant.program }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ registrant.registered_at }}
                                            </td>
                                            <td v-if="activeTab === 'Waitlisted'"
                                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                #{{ registrant.waitlist_position }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                                <!-- Promote from waitlist -->
                                                <button v-if="activeTab === 'Waitlisted' && registrant.can_be_promoted"
                                                    @click="promoteFromWaitlist(registrant)"
                                                    class="text-green-600 hover:text-green-900 transition duration-200"
                                                    title="Promote to registered">
                                                    <ArrowUpIcon class="h-5 w-5" />
                                                </button>

                                                <!-- Mark attendance -->
                                                <button v-if="activeTab === 'Registered'"
                                                    @click="toggleAttendance(registrant)" :class="[
                                                        'px-3 py-1 rounded text-xs font-medium transition duration-200',
                                                        registrant.attended
                                                            ? 'bg-green-100 text-green-800 hover:bg-green-200'
                                                            : 'bg-gray-100 text-gray-800 hover:bg-gray-200'
                                                    ]">
                                                    {{ registrant.attended ? 'Attended' : 'Mark Attended' }}
                                                </button>

                                                <!-- Remove registrant -->
                                                <button @click="removeRegistrant(registrant)"
                                                    class="text-red-600 hover:text-red-900 transition duration-200"
                                                    title="Remove registrant">
                                                    <TrashIcon class="h-5 w-5" />
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                                <!-- Empty state -->
                                <div v-if="currentRegistrants.length === 0" class="text-center py-8">
                                    <UsersIcon class="mx-auto h-12 w-12 text-gray-400" />
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">No {{ activeTab.toLowerCase() }}
                                        registrants</h3>
                                    <p class="mt-1 text-sm text-gray-500">
                                        {{ activeTab === 'Registered' ? 'No one has registered for this event yet.' :
                                        'No one is on the waitlist.' }}
                                    </p>
                                </div>
                            </div>

                            <!-- Export Button -->
                            <div class="mt-6 flex justify-end">
                                <button @click="exportRegistrants"
                                    class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition duration-200 text-sm font-medium">
                                    Export to CSV
                                </button>
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import {
    Dialog,
    DialogPanel,
    DialogTitle,
    TransitionChild,
    TransitionRoot,
} from '@headlessui/vue'
import {
    XMarkIcon,
    UsersIcon,
    ArrowUpIcon,
    TrashIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    show: {
        type: Boolean,
        required: true
    },
    event: {
        type: Object,
        required: true
    }
})

const emit = defineEmits(['close', 'registrant-removed', 'attendance-updated', 'waitlist-promoted'])

// Reactive data
const loading = ref(false)
const activeTab = ref('Registered')
const registrants = ref([])
const waitlisted = ref([])
const stats = ref({})

// Computed properties
const tabs = computed(() => [
    { name: 'Registered', count: registrants.value.length },
    { name: 'Waitlisted', count: waitlisted.value.length }
])

const currentRegistrants = computed(() => {
    return activeTab.value === 'Registered' ? registrants.value : waitlisted.value
})

// Methods
const loadRegistrants = async () => {
    loading.value = true
    try {
        const response = await fetch(`/events/${props.event.id}/registrants`)
        const data = await response.json()

        registrants.value = data.registrants
        waitlisted.value = data.waitlisted
        stats.value = data.stats
    } catch (error) {
        console.error('Failed to load registrants:', error)
        alert('Failed to load registrants')
    } finally {
        loading.value = false
    }
}

const getInitials = (name) => {
    return name.split(' ').map(n => n[0]).join('').toUpperCase()
}

const promoteFromWaitlist = async (registrant) => {
    if (!confirm(`Promote ${registrant.name} from waitlist to registered?`)) {
        return
    }

    try {
        const response = await fetch(`/events/${props.event.id}/registrants/${registrant.id}/promote`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })

        const data = await response.json()

        if (data.success) {
            await loadRegistrants()
            emit('waitlist-promoted', registrant)
            alert('User promoted successfully')
        } else {
            alert(data.message)
        }
    } catch (error) {
        console.error('Promotion failed:', error)
        alert('Promotion failed')
    }
}

const toggleAttendance = async (registrant) => {
    const newAttendanceStatus = !registrant.attended

    try {
        const response = await fetch(`/events/${props.event.id}/registrants/${registrant.id}/attendance`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                attended: newAttendanceStatus
            })
        })

        const data = await response.json()

        if (data.success) {
            await loadRegistrants()
            emit('attendance-updated', registrant)
        } else {
            alert(data.message)
        }
    } catch (error) {
        console.error('Attendance update failed:', error)
        alert('Attendance update failed')
    }
}

const removeRegistrant = async (registrant) => {
    if (!confirm(`Remove ${registrant.name} from this event?`)) {
        return
    }

    try {
        const response = await fetch(`/events/${props.event.id}/registrants/${registrant.id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })

        const data = await response.json()

        if (data.success) {
            await loadRegistrants()
            emit('registrant-removed', registrant)
            alert('Registrant removed successfully')
        } else {
            alert(data.message)
        }
    } catch (error) {
        console.error('Removal failed:', error)
        alert('Removal failed')
    }
}

const exportRegistrants = () => {
    const allRegistrants = [...registrants.value, ...waitlisted.value]
    const csvContent = [
        ['Name', 'Email', 'Program', 'Student ID', 'Status', 'Registered At', 'Attended'],
        ...allRegistrants.map(r => [
            r.name,
            r.email,
            r.program,
            r.student_id,
            r.status,
            r.registered_at,
            r.attended ? 'Yes' : 'No'
        ])
    ].map(row => row.join(',')).join('\n')

    const blob = new Blob([csvContent], { type: 'text/csv' })
    const url = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `registrants-${props.event.title}-${new Date().toISOString().split('T')[0]}.csv`
    document.body.appendChild(a)
    a.click()
    document.body.removeChild(a)
    window.URL.revokeObjectURL(url)
}

// Lifecycle
onMounted(() => {
    if (props.show) {
        loadRegistrants()
    }
})
</script>