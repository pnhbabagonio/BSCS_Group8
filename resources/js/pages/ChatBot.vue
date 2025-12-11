<template>
  <Head title="PSITS Chatbot" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <!-- changed: removed top padding (pt) by replacing p-4 with px-4 pb-4 -->
    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl px-4 pb-4">
      <!-- Header -->
      <div class="mb-4 flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-foreground">PSITS Chatbot Assistant</h1>
          <p class="text-muted-foreground">Ask questions about PSITS officers, events, and organization</p>
        </div>
        <div class="flex items-center gap-3">
          <Badge variant="outline" class="bg-blue-50 text-blue-700 border-blue-200">
            <MessageSquare class="mr-2 h-3 w-3" />
            AI Assistant
          </Badge>
          <Badge variant="outline" class="bg-green-50 text-green-700 border-green-200">
            <ExternalLink class="mr-2 h-3 w-3" />
            Connected
          </Badge>
        </div>
      </div>

      <div class="grid gap-6">
        <!-- Chat Interface -->
        <div class="grid gap-4 lg:grid-cols-3">
          <!-- Chat Container -->
          <div class="lg:col-span-2">
            <div class="rounded-xl border border-border bg-card p-6 h-full">
              <div class="mb-4 flex items-center justify-between">
                <h2 class="text-xl font-semibold">Chat with PSITS Assistant</h2>
                <MessageCircle class="h-5 w-5 text-muted-foreground" />
              </div>

              <!-- Chat Messages -->
              <div class="mb-4 h-[400px] overflow-y-auto rounded-lg border border-border bg-muted/30 p-4">
                <!-- Welcome Message -->
                <div class="mb-4">
                  <div class="flex items-start gap-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100">
                      <MessageSquare class="h-4 w-4 text-blue-600" />
                    </div>
                    <div class="flex-1">
                      <div class="rounded-lg bg-muted p-3">
                        <p class="text-sm">
                          Hello! I'm your PSITS chatbot assistant. I can help you with information about:
                        </p>
                        <ul class="mt-2 list-inside list-disc text-sm text-muted-foreground">
                          <li>PSITS officers and their roles</li>
                          <li>Upcoming events and activities</li>
                          <li>Organization information and contact details</li>
                          <li>Membership and participation</li>
                        </ul>
                        <p class="mt-2 text-sm">What would you like to know?</p>
                      </div>
                      <span class="mt-1 text-xs text-muted-foreground">Just now</span>
                    </div>
                  </div>
                </div>

                <!-- User Messages -->
                <div v-for="(message, index) in chatHistory" :key="index" class="mb-4">
                  <!-- User Message -->
                  <div class="mb-2 flex items-start gap-3 justify-end">
                    <div class="flex-1">
                      <div class="rounded-lg bg-blue-600 p-3 text-white">
                        <p class="text-sm">{{ message.user }}</p>
                      </div>
                      <span class="mt-1 text-xs text-muted-foreground text-right block">{{ formatTime(message.timestamp) }}</span>
                    </div>
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-600">
                      <Users class="h-4 w-4 text-white" />
                    </div>
                  </div>

                  <!-- Bot Response -->
                  <div v-if="message.botResponse" class="flex items-start gap-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100">
                      <MessageSquare class="h-4 w-4 text-blue-600" />
                    </div>
                    <div class="flex-1">
                      <div class="rounded-lg bg-muted p-3">
                        <p class="text-sm whitespace-pre-line">{{ message.botResponse }}</p>
                      </div>
                      <span class="mt-1 text-xs text-muted-foreground">{{ formatTime(message.timestamp) }}</span>
                    </div>
                  </div>
                </div>

                <!-- Loading Indicator -->
                <div v-if="loading" class="flex items-start gap-3">
                  <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100">
                    <MessageSquare class="h-4 w-4 text-blue-600" />
                  </div>
                  <div class="flex-1">
                    <div class="rounded-lg bg-muted p-3">
                      <div class="flex items-center gap-2">
                        <div class="h-2 w-2 animate-ping rounded-full bg-blue-600"></div>
                        <div class="h-2 w-2 animate-ping rounded-full bg-blue-600" style="animation-delay: 0.2s"></div>
                        <div class="h-2 w-2 animate-ping rounded-full bg-blue-600" style="animation-delay: 0.4s"></div>
                        <span class="text-sm">Thinking...</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Input Area -->
              <div class="space-y-3">
                <div class="flex gap-2">
                  <input
                    v-model="userMessage"
                    type="text"
                    placeholder="Type your question here..."
                    class="flex-1 px-4 py-2 border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-card"
                    @keyup.enter="sendTestRequest"
                    :disabled="loading"
                  />
                  <Button 
                    @click="sendTestRequest" 
                    :disabled="loading || !userMessage.trim()"
                    class="bg-blue-600 hover:bg-blue-700"
                  >
                    <Send class="h-4 w-4 mr-2" />
                    Send
                  </Button>
                </div>
                
                <!-- Sample Questions -->
                <div>
                  <p class="mb-2 text-sm text-muted-foreground">Quick questions:</p>
                  <div class="flex flex-wrap gap-2">
                    <Button
                      v-for="(question, index) in sampleQuestions"
                      :key="index"
                      @click="useSampleQuestion(question)"
                      variant="outline"
                      size="sm"
                      class="text-xs"
                      :disabled="loading"
                    >
                      {{ question }}
                    </Button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Info Panel -->
          <div class="space-y-4">
            <!-- Webhook Status -->
            <div class="rounded-xl border border-border bg-card p-6">
              <div class="mb-4 flex items-center justify-between">
                <h3 class="font-semibold">Connection Status</h3>
                <div class="h-2 w-2 rounded-full bg-green-500"></div>
              </div>
              <div class="space-y-3">
                <div class="flex items-center justify-between">
                  <span class="text-sm text-muted-foreground">Webhook URL</span>
                  <div class="flex items-center gap-1">
                    <code class="text-xs bg-muted px-2 py-1 rounded truncate max-w-[150px]">{{ webhookUrl }}</code>
                    <Button 
                      @click="copyToClipboard(webhookUrl)" 
                      variant="ghost" 
                      size="icon"
                      class="h-6 w-6"
                    >
                      <Copy class="h-3 w-3" />
                    </Button>
                  </div>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-sm text-muted-foreground">Last Response</span>
                  <span class="text-sm">{{ lastResponseTime || 'No response yet' }}</span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-sm text-muted-foreground">Response Time</span>
                  <span class="text-sm">{{ responseTime ? `${responseTime}ms` : '--' }}</span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-sm text-muted-foreground">Status Code</span>
                  <Badge :variant="statusClass === 'success' ? 'default' : 'destructive'">
                    {{ responseStatus || '--' }}
                  </Badge>
                </div>
              </div>
            </div>

            <!-- Quick Actions -->
            <div class="rounded-xl border border-border bg-card p-6">
              <div class="mb-4 flex items-center justify-between">
                <h3 class="font-semibold">Quick Actions</h3>
                <Zap class="h-5 w-5 text-muted-foreground" />
              </div>
              <div class="space-y-2">
                <Button 
                  @click="clearChat" 
                  variant="outline" 
                  size="sm" 
                  class="w-full justify-start"
                >
                  <Trash2 class="mr-2 h-4 w-4" />
                  Clear Chat
                </Button>
                <Button 
                  @click="copyChatHistory" 
                  variant="outline" 
                  size="sm" 
                  class="w-full justify-start"
                >
                  <Copy class="mr-2 h-4 w-4" />
                  Copy Chat
                </Button>
                <Button 
                  @click="exportChat" 
                  variant="outline" 
                  size="sm" 
                  class="w-full justify-start"
                >
                  <Download class="mr-2 h-4 w-4" />
                  Export Chat
                </Button>
              </div>
            </div>

            <!-- PSITS Information -->
            <div class="rounded-xl border border-border bg-card p-6">
              <div class="mb-4 flex items-center justify-between">
                <h3 class="font-semibold">About PSITS</h3>
                <Info class="h-5 w-5 text-muted-foreground" />
              </div>
              <div class="space-y-3">
                <div class="flex items-center gap-2">
                  <Users class="h-4 w-4 text-muted-foreground" />
                  <span class="text-sm">Philippine Society of IT Students</span>
                </div>
                <div class="flex items-center gap-2">
                  <Calendar class="h-4 w-4 text-muted-foreground" />
                  <span class="text-sm">Academic Year: {{ academicYear }}</span>
                </div>
                <div class="flex items-center gap-2">
                  <MapPin class="h-4 w-4 text-muted-foreground" />
                  <span class="text-sm">IT Building Room 305</span>
                </div>
                <div class="flex items-center gap-2">
                  <Mail class="h-4 w-4 text-muted-foreground" />
                  <span class="text-sm">psits@university.edu</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Technical Details (Collapsible) -->
        <div class="rounded-xl border border-border bg-card p-6">
          <div class="flex items-center justify-between mb-4 cursor-pointer" @click="showTechnicalDetails = !showTechnicalDetails">
            <h3 class="font-semibold">Technical Details</h3>
            <ChevronDown v-if="!showTechnicalDetails" class="h-5 w-5 text-muted-foreground" />
            <ChevronUp v-else class="h-5 w-5 text-muted-foreground" />
          </div>
          
          <div v-if="showTechnicalDetails" class="space-y-4">
            <!-- Request Payload -->
            <div>
              <h4 class="text-sm font-medium mb-2">Request Payload</h4>
              <div class="rounded-lg bg-muted p-3">
                <pre class="text-xs overflow-x-auto">{{ requestPayload }}</pre>
              </div>
            </div>

            <!-- Response Data -->
            <div v-if="lastResponseData">
              <h4 class="text-sm font-medium mb-2">Last Response</h4>
              <div class="rounded-lg bg-muted p-3">
                <pre class="text-xs overflow-x-auto">{{ JSON.stringify(lastResponseData, null, 2) }}</pre>
              </div>
            </div>

            <!-- System Info -->
            <div class="grid grid-cols-3 gap-4 text-sm">
              <div class="space-y-1">
                <span class="text-muted-foreground">AI Model</span>
                <p class="font-medium">GPT-4.1-mini</p>
              </div>
              <div class="space-y-1">
                <span class="text-muted-foreground">Memory</span>
                <p class="font-medium">10 messages</p>
              </div>
              <div class="space-y-1">
                <span class="text-muted-foreground">API Version</span>
                <p class="font-medium">1.0.0</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { ref, computed } from 'vue';
import {
  MessageSquare,
  MessageCircle,
  Users,
  Calendar,
  MapPin,
  Mail,
  Info,
  Zap,
  Trash2,
  Copy,
  Download,
  ChevronDown,
  ChevronUp,
  ExternalLink,
  Send,
} from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Chatbot', href: '/chatbot' },
];

// Chat data
const userMessage = ref('');
const loading = ref(false);
const webhookUrl = 'https://mariaclara1886.app.n8n.cloud/webhook-test/psits-chatbot';
const responseStatus = ref<number | null>(null);
const responseTime = ref<number | null>(null);
const lastResponseData = ref<any>(null);
const lastResponseTime = ref<string>('');
const showTechnicalDetails = ref(false);
const academicYear = '2024-2025';

// Chat history
const chatHistory = ref<Array<{
  user: string;
  botResponse?: string;
  timestamp: Date;
}>>([]);

// Sample questions
const sampleQuestions = [
  'Who is the current president?',
  'What are the upcoming events?',
  'How can I contact PSITS?',
  'When is IT Week?',
  'Who are the officers?'
];

// Computed
const requestPayload = computed(() => {
  return JSON.stringify({ message: userMessage.value }, null, 2);
});

const statusClass = computed(() => {
  if (!responseStatus.value) return '';
  return responseStatus.value >= 200 && responseStatus.value < 300 ? 'success' : 'error';
});

// Methods
const sendTestRequest = async () => {
  if (!userMessage.value.trim()) return;

  const message = userMessage.value;
  const startTime = Date.now();

  // Add user message to chat
  chatHistory.value.push({
    user: message,
    timestamp: new Date(),
  });

  loading.value = true;
  responseStatus.value = null;
  lastResponseData.value = null;

  try {
    const response = await fetch(webhookUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ message }),
    });

    const endTime = Date.now();
    responseTime.value = endTime - startTime;
    responseStatus.value = response.status;
    lastResponseTime.value = new Date().toLocaleTimeString();

    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

    // try parse JSON, fallback to text
    let data: any;
    try {
      data = await response.json();
    } catch {
      data = await response.text();
    }

    lastResponseData.value = data;

    // Normalize and pick meaningful text, ignore metadata-only objects
    let botText = '';

    if (typeof data === 'string' && data.trim()) {
      botText = data.trim();
    } else if (data && typeof data === 'object') {
      // prefer common fields
      const candidates = [
        data.response,
        data.text,
        data.message,
        data.output,
        data.answer,
      ];

      for (const c of candidates) {
        if (typeof c === 'string' && c.trim()) {
          botText = c.trim();
          break;
        }
      }

      // if nothing from common fields, search object values for first non-empty string
      if (!botText) {
        const vals = Object.values(data)
          .map(v => (typeof v === 'string' ? v.trim() : (v ? JSON.stringify(v) : '')))
          .filter(v => v && v !== '{}' && v !== '[]');
        botText = vals.find(v => v.length > 0) ?? '';
      }
    }

    if (!botText) {
      botText = 'No response from assistant.';
    }

    // attach sanitized response to last chat entry
    const lastMessageIndex = chatHistory.value.length - 1;
    chatHistory.value[lastMessageIndex].botResponse = botText;

    showToast('Response received successfully!', 'success');
  } catch (err: any) {
    const lastMessageIndex = chatHistory.value.length - 1;
    chatHistory.value[lastMessageIndex].botResponse = `Error: ${err.message}`;
    showToast(`Error: ${err.message}`, 'error');
  } finally {
    loading.value = false;
    userMessage.value = '';
  }
};

const useSampleQuestion = (question: string) => {
  userMessage.value = question;
  showToast('Question loaded. Press Enter or click Send to submit.', 'info');
};

const clearChat = () => {
  chatHistory.value = [];
  lastResponseData.value = null;
  responseStatus.value = null;
  responseTime.value = null;
  lastResponseTime.value = '';
  showToast('Chat cleared', 'info');
};

const copyChatHistory = () => {
  const chatText = chatHistory.value.map(msg => 
    `You: ${msg.user}\nBot: ${msg.botResponse || '...'}\n---`
  ).join('\n');
  
  copyToClipboard(chatText);
  showToast('Chat copied to clipboard', 'success');
};

const exportChat = () => {
  const chatText = chatHistory.value.map(msg => 
    `Timestamp: ${formatTime(msg.timestamp)}\nYou: ${msg.user}\nBot: ${msg.botResponse || '...'}\n`
  ).join('\n---\n');
  
  const blob = new Blob([chatText], { type: 'text/plain' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `psits-chat-${new Date().toISOString().slice(0, 10)}.txt`;
  a.click();
  URL.revokeObjectURL(url);
  
  showToast('Chat exported', 'success');
};

const copyToClipboard = (text: string) => {
  navigator.clipboard.writeText(text);
};

const formatTime = (date: Date) => {
  return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
};

const showToast = (message: string, type: 'success' | 'error' | 'info' = 'info') => {
  // You can implement a toast notification system here
  console.log(`${type}: ${message}`);
};
</script>

<style>
/* Custom scrollbar */
:deep(.h-\[400px\])::-webkit-scrollbar {
  width: 6px;
}

:deep(.h-\[400px\])::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

:deep(.h-\[400px\])::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 3px;
}

:deep(.h-\[400px\])::-webkit-scrollbar-thumb:hover {
  background: #555;
}

/* Animation for typing dots */
@keyframes ping {
  0%, 100% {
    opacity: 1;
    transform: scale(1);
  }
  50% {
    opacity: 0.5;
    transform: scale(0.8);
  }
}

.animate-ping {
  animation: ping 1.5s ease-in-out infinite;
}
</style>