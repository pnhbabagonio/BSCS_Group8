<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Alert, AlertDescription } from '@/components/ui/alert'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Badge } from '@/components/ui/badge'
import { Search, UserPlus, Loader2, Users, X, CheckCircle, XCircle } from 'lucide-vue-next'
import { watch } from 'vue'

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

interface User {
    id: number
    name: string
    email: string
    student_id: string
    program: string
    year: string
    role: string
    status: string
}

interface Attendee {
    id: number
    user_id: number
    event_id: number
    user_name: string
    user_email: string
    student_id: string
    program: string
    attendance_status: string
    registered_at: string
}

const props = defineProps<{
    events: Event[]
}>()

// State
const selectedEventId = ref<number | null>(null)
const searchQuery = ref('')
const isSubmitting = ref(false)
const errors = ref<Record<string, string>>({})
const users = ref<User[]>([])
const eventAttendees = ref<Attendee[]>([])
const selectedUsers = ref<number[]>([])
const isLoadingUsers = ref(false)
const isLoadingAttendees = ref(false)

// Computed
const selectedEvent = computed(() => {
    return props.events.find(event => event.id === selectedEventId.value) || null
})

const filteredUsers = computed(() => {
    if (!searchQuery.value.trim()) return users.value
    const query = searchQuery.value.toLowerCase()
    return users.value.filter(user => 
        user.name.toLowerCase().includes(query) ||
        user.email.toLowerCase().includes(query) ||
        user.student_id?.toLowerCase().includes(query) ||
        user.program?.toLowerCase().includes(query)
    )
})

const availableUsers = computed(() => {
    const registeredUserIds = eventAttendees.value.map(attendee => attendee.user_id)
    return filteredUsers.value.filter(user => !registeredUserIds.includes(user.id))
})

const isEventSelected = computed(() => selectedEventId.value !== null)

const canRegisterMore = computed(() => {
    if (!selectedEvent.value) return false
    return selectedEvent.value.registered < selectedEvent.value.capacity
})

// Methods
const fetchUsers = async () => {
    isLoadingUsers.value = true
    try {
        const response = await fetch('/user-management-data') // We'll create this route
        if (response.ok) {
            const data = await response.json()
            users.value = data.users || []
        }
    } catch (error) {
        console.error('Failed to fetch users:', error)
        errors.value.general = 'Failed to load users'
    } finally {
        isLoadingUsers.value = false
    }
}

const fetchEventAttendees = async (eventId: number) => {
    isLoadingAttendees.value = true
    try {
        const response = await fetch(`/events/${eventId}/attendees`) // We'll create this route
        if (response.ok) {
            const data = await response.json()
            eventAttendees.value = data.attendees || []
        }
    } catch (error) {
        console.error('Failed to fetch attendees:', error)
        errors.value.general = 'Failed to load attendees'
    } finally {
        isLoadingAttendees.value = false
    }
}

const toggleUserSelection = (userId: number) => {
    const index = selectedUsers.value.indexOf(userId)
    if (index > -1) {
        selectedUsers.value.splice(index, 1)
    } else {
        selectedUsers.value.push(userId)
    }
}

const registerSelectedUsers = async () => {
    if (!selectedEventId.value || selectedUsers.value.length === 0) return

    isSubmitting.value = true
    errors.value = {}

    try {
        // Use Inertia post method instead of fetch
        router.post(`/events/${selectedEventId.value}/register-attendees`, {
            user_ids: selectedUsers.value
        }, {
            onSuccess: () => {
                // Refresh attendees list
                fetchEventAttendees(selectedEventId.value!)
                // Clear selection
                selectedUsers.value = []
                searchQuery.value = ''
            },
            onError: (serverErrors) => {
                errors.value = serverErrors
            },
            onFinish: () => {
                isSubmitting.value = false
            }
        })
    } catch (error) {
        errors.value.general = 'Network error occurred'
        isSubmitting.value = false
    }
}

const removeAttendee = async (attendeeId: number) => {
    if (!confirm('Are you sure you want to remove this attendee?')) return

    try {
        router.delete(`/attendees/${attendeeId}`, {
            onSuccess: () => {
                fetchEventAttendees(selectedEventId.value!)
            },
            onError: () => {
                errors.value.general = 'Failed to remove attendee'
            }
        })
    } catch (error) {
        console.error('Failed to remove attendee:', error)
    }
}

const updateAttendanceStatus = async (attendeeId: number, status: string) => {
    try {
        router.put(`/attendees/${attendeeId}`, {
            attendance_status: status
        }, {
            onSuccess: () => {
                fetchEventAttendees(selectedEventId.value!)
            },
            onError: () => {
                errors.value.general = 'Failed to update attendance status'
            }
        })
    } catch (error) {
        console.error('Failed to update attendance status:', error)
    }
}

const clearSelection = () => {
    selectedUsers.value = []
    searchQuery.value = ''
}

// Watch for event selection changes
watch(selectedEventId, (newEventId) => {
    if (newEventId) {
        fetchEventAttendees(newEventId)
    } else {
        eventAttendees.value = []
    }
})

// Lifecycle
onMounted(() => {
    fetchUsers()
})
</script>

<template>
    <div class="space-y-6">
        <!-- Event Selection -->
        <Card>
            <CardHeader>
                <CardTitle>Select Event</CardTitle>
                <CardDescription>Choose an event to manage attendee registrations</CardDescription>
            </CardHeader>
            <CardContent>
                <div class="space-y-4">
                    <div class="space-y-2">
                        <Label for="event-select">Event</Label>
                        <Select v-model="selectedEventId">
                            <SelectTrigger>
                                <SelectValue placeholder="Select an event" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem 
                                    v-for="event in events.filter(e => e.status !== 'cancelled')" 
                                    :key="event.id" 
                                    :value="event.id"
                                >
                                    {{ event.title }} ({{ event.date }})
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <!-- Event Info -->
                    <div v-if="selectedEvent" class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-muted rounded-lg">
                        <div class="space-y-1">
                            <p class="text-sm font-medium">Capacity</p>
                            <p class="text-2xl font-bold">{{ selectedEvent.registered }} / {{ selectedEvent.capacity }}</p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-sm font-medium">Status</p>
                            <Badge :variant="selectedEvent.status === 'upcoming' ? 'default' : 
                                          selectedEvent.status === 'ongoing' ? 'secondary' : 'outline'">
                                {{ selectedEvent.status }}
                            </Badge>
                        </div>
                        <div class="space-y-1">
                            <p class="text-sm font-medium">Availability</p>
                            <Badge :variant="selectedEvent.is_full ? 'destructive' : 'default'">
                                {{ selectedEvent.is_full ? 'Full' : `${selectedEvent.capacity - selectedEvent.registered} spots left` }}
                            </Badge>
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- Manual Registration Section -->
        <div v-if="isEventSelected" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- User Selection -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <UserPlus class="h-5 w-5" />
                        Register Attendees
                    </CardTitle>
                    <CardDescription>
                        Search and select users to register for this event
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <!-- Search -->
                        <div class="space-y-2">
                            <Label for="user-search">Search Users</Label>
                            <div class="relative">
                                <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground h-4 w-4" />
                                <Input
                                    id="user-search"
                                    v-model="searchQuery"
                                    type="text"
                                    placeholder="Search by name, email, or student ID..."
                                    class="pl-10"
                                />
                            </div>
                        </div>

                        <!-- Error Alert -->
                        <Alert v-if="errors.general" variant="destructive">
                            <AlertDescription>{{ errors.general }}</AlertDescription>
                        </Alert>

                        <!-- Capacity Warning -->
                        <Alert v-if="!canRegisterMore" variant="destructive">
                            <AlertDescription>
                                This event is at full capacity. Cannot register more attendees.
                            </AlertDescription>
                        </Alert>

                        <!-- Selected Users Count -->
                        <div v-if="selectedUsers.length > 0" class="flex items-center justify-between p-3 bg-primary/10 rounded-lg">
                            <span class="text-sm font-medium">
                                {{ selectedUsers.length }} user(s) selected
                            </span>
                            <Button @click="clearSelection" variant="ghost" size="sm">
                                <X class="h-4 w-4" />
                            </Button>
                        </div>

                        <!-- Users List -->
                        <div class="space-y-2 max-h-96 overflow-y-auto">
                            <div
                                v-for="user in availableUsers"
                                :key="user.id"
                                @click="toggleUserSelection(user.id)"
                                class="flex items-center justify-between p-3 border rounded-lg cursor-pointer transition-colors hover:bg-muted/50"
                                :class="selectedUsers.includes(user.id) ? 'bg-primary/10 border-primary' : ''"
                            >
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-sm truncate">{{ user.name }}</p>
                                    <p class="text-xs text-muted-foreground truncate">{{ user.email }}</p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <Badge variant="outline" class="text-xs">
                                            {{ user.student_id || 'No ID' }}
                                        </Badge>
                                        <Badge variant="secondary" class="text-xs">
                                            {{ user.program }}
                                        </Badge>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 ml-2">
                                    <CheckCircle 
                                        v-if="selectedUsers.includes(user.id)" 
                                        class="h-5 w-5 text-primary" 
                                    />
                                </div>
                            </div>

                            <div v-if="availableUsers.length === 0 && !isLoadingUsers" class="text-center py-8 text-muted-foreground">
                                <Users class="h-12 w-12 mx-auto mb-2 opacity-50" />
                                <p>No users found</p>
                            </div>

                            <div v-if="isLoadingUsers" class="text-center py-8">
                                <Loader2 class="h-8 w-8 animate-spin mx-auto mb-2" />
                                <p>Loading users...</p>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-2 pt-4">
                            <Button
                                @click="clearSelection"
                                variant="outline"
                                class="flex-1"
                                :disabled="selectedUsers.length === 0"
                            >
                                Clear Selection
                            </Button>
                            <Button
                                @click="registerSelectedUsers"
                                class="flex-1 gap-2"
                                :disabled="selectedUsers.length === 0 || !canRegisterMore || isSubmitting"
                            >
                                <Loader2 v-if="isSubmitting" class="h-4 w-4 animate-spin" />
                                <UserPlus v-else class="h-4 w-4" />
                                {{ isSubmitting ? 'Registering...' : `Register (${selectedUsers.length})` }}
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Current Attendees -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Users class="h-5 w-5" />
                        Current Attendees ({{ eventAttendees.length }})
                    </CardTitle>
                    <CardDescription>
                        Manage existing registrations for this event
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <!-- Attendees Table -->
                        <div class="border rounded-lg">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Name</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead class="text-right">Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="attendee in eventAttendees" :key="attendee.id">
                                        <TableCell>
                                            <div>
                                                <p class="font-medium text-sm">{{ attendee.user_name }}</p>
                                                <p class="text-xs text-muted-foreground">{{ attendee.user_email }}</p>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <Select 
                                                :value="attendee.attendance_status" 
                                                @update:value="(value:any) => updateAttendanceStatus(attendee.id, value)"
                                            >
                                                <SelectTrigger class="w-32">
                                                    <SelectValue />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="registered">Registered</SelectItem>
                                                    <SelectItem value="attended">Attended</SelectItem>
                                                    <SelectItem value="cancelled">Cancelled</SelectItem>
                                                </SelectContent>
                                            </Select>
                                        </TableCell>
                                        <TableCell class="text-right">
                                            <Button
                                                @click="removeAttendee(attendee.id)"
                                                variant="ghost"
                                                size="sm"
                                                class="text-destructive hover:text-destructive hover:bg-destructive/10"
                                            >
                                                <X class="h-4 w-4" />
                                            </Button>
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>

                            <div v-if="eventAttendees.length === 0 && !isLoadingAttendees" class="text-center py-8 text-muted-foreground">
                                <Users class="h-12 w-12 mx-auto mb-2 opacity-50" />
                                <p>No attendees registered yet</p>
                            </div>

                            <div v-if="isLoadingAttendees" class="text-center py-8">
                                <Loader2 class="h-8 w-8 animate-spin mx-auto mb-2" />
                                <p>Loading attendees...</p>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- No Event Selected Message -->
        <div v-else class="text-center py-12 text-muted-foreground">
            <Users class="h-16 w-16 mx-auto mb-4 opacity-50" />
            <h3 class="text-lg font-medium mb-2">No Event Selected</h3>
            <p>Please select an event to manage attendee registrations</p>
        </div>
    </div>
</template>