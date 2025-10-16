<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';

const features = ref([
    {
        title: 'Instant QR Payments',
        description: 'Generate and scan QR codes for seamless transactions with real-time processing.',
        icon: 'M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M12 12h-4.01M12 12v4m6-4h-2M6 12h2m0-6V4m0 0h2m-2 0H4',
        color: 'text-cyan-400',
        gradient: 'from-cyan-400 to-blue-500',
    },
    {
        title: 'Neural Analytics',
        description: 'AI-powered financial insights with quantum-speed data processing.',
        icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
        color: 'text-emerald-400',
        gradient: 'from-emerald-400 to-green-500',
    },
    {
        title: 'Quantum Automation',
        description: 'Lightning-fast automation powered by next-gen algorithms.',
        icon: 'M13 10V3L4 14h7v7l9-11h-7z',
        color: 'text-purple-400',
        gradient: 'from-purple-400 to-pink-500',
    },
    {
        title: 'Hive Mind Connect',
        description: 'Advanced member engagement with predictive behavior analytics.',
        icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
        color: 'text-rose-400',
        gradient: 'from-rose-400 to-orange-500',
    },
]);

const loaded = ref(false);
const activeFeature = ref(0);
const glitchText = ref(false);

onMounted(() => {
    setTimeout(() => {
        loaded.value = true;
    }, 100);

    // Rotate active feature every 4 seconds
    setInterval(() => {
        activeFeature.value = (activeFeature.value + 1) % features.value.length;
    }, 4000);

    // Random glitch effect on title
    setInterval(() => {
        glitchText.value = true;
        setTimeout(() => {
            glitchText.value = false;
        }, 200);
    }, 8000);
});
</script>

<template>
    <Head title="PSITS Nexus - Cybernetic Payment Matrix">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
        <meta
            name="description"
            content="Next-generation digital payment matrix for organizations with quantum QR technology, neural automation, and cybernetic analytics."
        />
    </Head>

    <div
        class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 text-gray-100 transition-all duration-1000 relative overflow-hidden"
        :class="{ 'opacity-100': loaded, 'opacity-0': !loaded }"
    >
        <!-- Dynamic Grid Background -->
        <div class="fixed inset-0 opacity-30">
            <div class="absolute inset-0 bg-grid-pattern"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-transparent to-slate-900"></div>
        </div>

        <!-- Animated Background Elements -->
        <div class="pointer-events-none fixed inset-0 overflow-hidden">
            <!-- Floating orbs with glow -->
            <div class="absolute top-20 left-20 h-64 w-64 rounded-full bg-cyan-500/20 blur-3xl animate-pulse-slow shadow-glow-cyan"></div>
            <div class="absolute right-20 bottom-20 h-64 w-64 rounded-full bg-purple-500/20 blur-3xl animate-pulse-slow delay-1000 shadow-glow-purple"></div>
            <div class="absolute top-1/2 left-1/2 h-96 w-96 rounded-full bg-emerald-500/10 blur-3xl animate-pulse-slow delay-2000 transform -translate-x-1/2 -translate-y-1/2"></div>
            
            <!-- Scanning lines -->
            <div class="absolute inset-0">
                <div class="scan-line absolute top-0 left-0 h-0.5 w-full bg-gradient-to-r from-transparent via-cyan-400 to-transparent opacity-60 animate-scan-vertical"></div>
                <div class="scan-line absolute top-0 left-0 h-full w-0.5 bg-gradient-to-b from-transparent via-purple-400 to-transparent opacity-40 animate-scan-horizontal"></div>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="container mx-auto flex items-center justify-between px-6 py-6 relative z-10">
            <div class="flex items-center space-x-3">
                <div class="relative flex items-center justify-center">
                    <!-- Logo with stronger highlight -->
                    <AppLogoIcon class="h-15 w-15 " />

                    <!-- Halo ring (no blur) -->
                    <div class="absolute inset-0 h-15 w-15 rounded-full border-2 border-purple-400/60 animate-[ping_3s_ease-in-out_infinite]"></div>
                </div>

                <span class="text-xl font-bold bg-gradient-to-r from-cyan-400 to-purple-400 bg-clip-text text-transparent">
                    PSITS NEXUS
                </span>
            </div>
            <div class="flex items-center space-x-4">
                <Link
                    v-if="$page.props.auth.user"
                    :href="route('dashboard')"
                    class="cyber-button bg-gradient-to-r from-cyan-500 to-blue-600 px-6 py-3 font-medium text-white transition-all duration-300 hover:shadow-glow-cyan hover:scale-105"
                >
                    <span class="relative z-10">ENTER MATRIX</span>
                </Link>
                <template v-else>
                    <Link 
                        :href="route('login')" 
                        class="cyber-button-outline px-5 py-2 transition-all duration-300 hover:bg-slate-800/50 hover:shadow-glow-purple"
                    >
                        SIGN IN
                    </Link>
                    <Link
                        :href="route('register')"
                        class="cyber-button bg-gradient-to-r from-purple-500 to-pink-600 px-6 py-3 font-medium text-white transition-all duration-300 hover:shadow-glow-purple hover:scale-105"
                    >
                        <span class="relative z-10">JOIN US!</span>
                    </Link>
                </template>
            </div>
        </nav>

        <!-- Hero Section -->
        <main class="container mx-auto px-6 py-12 md:py-24 relative z-10">
            <div class="flex flex-col items-center gap-12 lg:flex-row">
                <!-- Content -->
                <div class="space-y-8 lg:w-1/2">
                    <h1 class="text-4xl leading-tight font-bold md:text-6xl">
                        <span class="block" :class="{ 'glitch': glitchText }">Philippine Society of</span>
                        <span class="block bg-gradient-to-r from-cyan-400 via-purple-400 to-pink-400 bg-clip-text text-transparent animate-gradient">
                            Information Technology
                        </span>
                        <span
                            class="block text-2xl md:text-3xl font-medium mt-3
                                    bg-gradient-to-r from-cyan-400 via-purple-400 to-pink-400
                                    bg-clip-text text-transparent animate-gradient"
                            >Students 
                                <span class="font-extrabold tracking-wide text-cyan-400 drop-shadow-[0_0_10px_rgba(34,211,238,0.8)]">
                                    USM - Chapter
                                </span>
                        </span>
                    </h1>
                    
                    <p class="max-w-lg text-lg text-gray-300 leading-relaxed">
                        Enter the next dimension of digital transactions. PSITS Nexus delivers 
                        <span class="text-cyan-400 font-semibold">quantum-speed</span> QR payments, 
                        <span class="text-purple-400 font-semibold">AI-powered</span> automation, and 
                        <span class="text-emerald-400 font-semibold">neural</span> analytics.
                    </p>

                    <div class="flex flex-col gap-6 sm:flex-row">
                        <Link
                            v-if="!$page.props.auth.user"
                            :href="route('register')"
                            class="cyber-button-primary group relative overflow-hidden px-8 py-4 font-bold text-white transition-all duration-300 hover:scale-105"
                        >
                            <span class="relative z-10 flex items-center justify-center">
                                <svg class="mr-3 h-5 w-5 transition-transform group-hover:rotate-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                Join us Now
                            </span>
                        </Link>
                        <Link
                            v-else
                            :href="route('dashboard')"
                            class="cyber-button-primary group relative overflow-hidden px-8 py-4 font-bold text-white transition-all duration-300 hover:scale-105"
                        >
                            <span class="relative z-10 flex items-center justify-center">
                                <svg class="mr-3 h-5 w-5 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                                ENTER MATRIX
                            </span>
                        </Link>
                        <a
                            href="#features"
                            class="cyber-button-secondary group flex items-center justify-center px-8 py-4 font-bold transition-all duration-300 hover:scale-105"
                        >
                            <svg class="mr-3 h-5 w-5 transition-transform group-hover:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 7.172V5L8 4z" />
                            </svg>
                            What we Offer?
                        </a>
                    </div>
                </div>

                <!-- Visual -->
                <div class="relative lg:w-1/2">
                    <div class="relative z-10 mx-auto w-full max-w-md">
                        <!-- Main Device Frame -->
                        <div class="cyber-frame aspect-square overflow-hidden bg-gradient-to-br from-slate-800/80 to-slate-900/80 backdrop-blur-sm border border-cyan-500/30 shadow-glow-cyan">
                            <!-- Holographic Display -->
                            <div class="relative h-full w-full overflow-hidden">
                                <div
                                    v-for="(feature, index) in features"
                                    :key="index"
                                    class="absolute inset-0 flex flex-col items-center justify-center p-8 transition-all duration-700 transform"
                                    :class="{ 
                                        'opacity-100 scale-100': activeFeature === index, 
                                        'opacity-0 scale-95': activeFeature !== index 
                                    }"
                                >
                                    <!-- Feature Icon with Glow -->
                                    <div
                                        class="mb-6 flex h-20 w-20 items-center justify-center rounded-xl relative"
                                        :class="`bg-gradient-to-br ${feature.gradient} p-0.5`"
                                    >
                                        <div class="h-full w-full rounded-xl bg-slate-900/90 flex items-center justify-center">
                                            <svg 
                                                class="h-10 w-10 transition-all duration-300" 
                                                :class="`${feature.color} drop-shadow-glow`" 
                                                fill="none" 
                                                stroke="currentColor" 
                                                viewBox="0 0 24 24"
                                            >
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="feature.icon" />
                                            </svg>
                                        </div>
                                        <!-- Rotating Ring -->
                                        <div class="absolute inset-0 rounded-xl border-2 border-transparent bg-gradient-to-r from-transparent via-current to-transparent opacity-50 animate-spin-slow" :class="feature.color"></div>
                                    </div>
                                    
                                    <h3 class="mb-4 text-center text-xl font-bold bg-gradient-to-r from-white to-gray-300 bg-clip-text text-transparent">
                                        {{ feature.title }}
                                    </h3>
                                    <p class="text-center text-gray-400 leading-relaxed">{{ feature.description }}</p>
                                    
                                    <!-- Data Stream Lines -->
                                    <div class="absolute bottom-4 left-4 right-4">
                                        <div class="h-1 bg-gradient-to-r from-transparent via-current to-transparent opacity-60 animate-pulse" :class="feature.color"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Floating QR Hologram -->
                        <div class="absolute -right-8 -bottom-8 h-36 w-36 cyber-frame bg-gradient-to-br from-slate-800/90 to-slate-900/90 p-4 animate-float border-purple-500/30 shadow-glow-purple">
                            <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-purple-600/20 to-cyan-600/20 rounded-lg relative overflow-hidden">
                                <!-- QR Pattern -->
                                <div class="grid h-24 w-24 grid-cols-6 gap-0.5">
                                    <div
                                        v-for="i in 36"
                                        :key="i"
                                        class="rounded-sm transition-all duration-1000"
                                        :class="Math.random() > 0.4 ? 'bg-cyan-400 shadow-glow-cyan' : 'bg-transparent'"
                                        :style="{ animationDelay: `${i * 100}ms` }"
                                    ></div>
                                </div>
                                <!-- Scan Line -->
                                <div class="absolute inset-0 bg-gradient-to-b from-transparent via-cyan-400/50 to-transparent h-1 animate-scan-qr"></div>
                            </div>
                        </div>

                        <!-- Data Points -->
                        <div class="absolute -left-6 top-1/4 w-3 h-3 rounded-full bg-cyan-400 shadow-glow-cyan animate-ping"></div>
                        <div class="absolute -right-4 top-1/3 w-2 h-2 rounded-full bg-purple-400 shadow-glow-purple animate-ping delay-1000"></div>
                        <div class="absolute left-1/4 -top-4 w-4 h-4 rounded-full bg-emerald-400 shadow-glow-emerald animate-ping delay-2000"></div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Features Section -->
        <section id="features" class="container mx-auto px-6 py-16 md:py-24 relative z-10">
            <div class="mb-16 text-center">
                <h2 class="mb-6 text-3xl font-bold md:text-5xl">
                    <span class="bg-gradient-to-r from-cyan-400 via-purple-400 to-pink-400 bg-clip-text text-transparent">
                        PSITS NEXUS CAPABILITIES
                    </span>
                </h2>
                <p class="mx-auto max-w-3xl text-lg text-gray-300 leading-relaxed">
                    Harness the power of quantum computing and artificial intelligence to revolutionize 
                    your organization's financial ecosystem with bleeding-edge technology.
                </p>
            </div>

            <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-4">
                <div
                    v-for="(feature, index) in features"
                    :key="index"
                    class="cyber-card group relative overflow-hidden p-8 transition-all duration-500 hover:-translate-y-3 hover:scale-105"
                    :style="{ animationDelay: `${index * 200}ms` }"
                >
                    <!-- Card Glow Effect -->
                    <div class="absolute inset-0 bg-gradient-to-br opacity-0 group-hover:opacity-20 transition-opacity duration-500" :class="feature.gradient"></div>
                    
                    <!-- Feature Icon -->
                    <div class="mb-8 flex h-16 w-16 items-center justify-center rounded-lg relative" :class="`bg-gradient-to-br ${feature.gradient} p-0.5`">
                        <div class="h-full w-full rounded-lg bg-slate-900/90 flex items-center justify-center">
                            <svg class="h-8 w-8 transition-all duration-300 group-hover:scale-110" :class="`${feature.color} drop-shadow-glow`" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="feature.icon" />
                            </svg>
                        </div>
                    </div>
                    
                    <h3 class="mb-4 text-xl font-bold text-white group-hover:text-cyan-400 transition-colors duration-300">
                        {{ feature.title }}
                    </h3>
                    <p class="text-gray-400 group-hover:text-gray-300 transition-colors duration-300 leading-relaxed">
                        {{ feature.description }}
                    </p>

                    <!-- Data Stream -->
                    <div class="absolute bottom-4 left-8 right-8">
                        <div class="h-px bg-gradient-to-r from-transparent via-current to-transparent opacity-0 group-hover:opacity-60 transition-opacity duration-500" :class="feature.color"></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="relative py-16 md:py-24 overflow-hidden">
            <!-- Background Effects -->
            <div class="absolute inset-0 bg-gradient-to-r from-purple-900/50 via-slate-900/80 to-cyan-900/50"></div>
            <div class="absolute inset-0 cyber-grid opacity-20"></div>
            
            <div class="container mx-auto px-6 text-center relative z-10">
                <h2 class="mb-8 text-3xl font-bold md:text-5xl">
                    <span class="bg-gradient-to-r from-cyan-400 to-purple-400 bg-clip-text text-transparent">
                        READY TO JOIN THE PSITS-NEXUS?
                    </span>
                </h2>
                <p class="mx-auto mb-12 max-w-3xl text-lg text-gray-300 leading-relaxed">
                    Join the evolution. Thousands of neural organizations are already using PSITS Nexus 
                    to quantum-leap their financial operations into the future.
                </p>
                
                <Link
                    :href="route($page.props.auth.user ? 'dashboard' : 'register')"
                    class="cyber-button-mega group relative inline-flex items-center justify-center px-12 py-6 font-bold text-white transition-all duration-500 hover:scale-110"
                >
                    <span class="relative z-10 flex items-center">
                        {{ $page.props.auth.user ? 'ENTER THE MATRIX' : 'SIGN UP HERE' }}
                        <svg class="ml-4 h-6 w-6 transition-transform group-hover:translate-x-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </span>
                </Link>
            </div>
        </section>

        <!-- Footer -->
        <footer class="relative bg-slate-900/80 backdrop-blur-sm border-t border-cyan-500/20 py-12">
            <div class="absolute inset-0 bg-gradient-to-r from-purple-900/10 via-slate-900/20 to-cyan-900/10"></div>
            
            <div class="container mx-auto px-6 relative z-10">
                <div class="flex flex-col items-center justify-between md:flex-row">
                    <div class="mb-6 flex items-center space-x-4 md:mb-0">
                        <div class="relative">
                            <AppLogoIcon class="h-12 w-12 fill-current text-cyan-400 drop-shadow-glow-cyan" />
                            <div class="absolute inset-0 h-12 w-12 bg-cyan-400/10 rounded-full blur-md animate-pulse"></div>
                        </div>
                        <div>
                            <span class="text-xl font-bold bg-gradient-to-r from-cyan-400 to-purple-400 bg-clip-text text-transparent block">
                                PSITS NEXUS
                            </span>
                            <span class="text-xs text-gray-500 font-mono">v1.0 </span>
                        </div>
                    </div>
                    
                    <div class="flex space-x-8">
                        <a href="#" class="cyber-link text-gray-400 hover:text-cyan-400 transition-colors duration-300 relative">
                            PROTOCOLS
                        </a>
                        <a href="#" class="cyber-link text-gray-400 hover:text-purple-400 transition-colors duration-300 relative">
                            PRIVACY_MATRIX
                        </a>
                        <a href="#" class="cyber-link text-gray-400 hover:text-emerald-400 transition-colors duration-300 relative">
                            NEURAL_LINK
                        </a>
                    </div>
                </div>
                
                <div class="mt-8 border-t border-cyan-500/20 pt-8 text-center">
                    <p class="text-sm text-gray-500 font-mono">
                        © 2077 PSITS NEXUS CORP. // ALL NEURAL RIGHTS RESERVED // QUANTUM ENCRYPTED
                    </p>
                    <div class="mt-2 flex justify-center space-x-4 text-xs text-gray-600">
                        <span>STATUS: ONLINE</span>
                        <span class="text-emerald-400">●</span>
                        <span>QUANTUM_SYNC: ACTIVE</span>
                        <span class="text-cyan-400">●</span>
                        <span>NEURAL_NET: CONNECTED</span>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
/* Cyberpunk Button Styles */
.cyber-button {
    border-radius: 0.5rem;
    border: 1px solid transparent;
    position: relative;
    overflow: hidden;
    clip-path: polygon(0 0, calc(100% - 10px) 0, 100% 100%, 10px 100%);
}

.cyber-button-outline {
    border-radius: 0.5rem;
    border: 2px solid rgba(168, 85, 247, 0.5);
    font-weight: 500;
    position: relative;
    clip-path: polygon(0 0, calc(100% - 8px) 0, 100% 100%, 8px 100%);
}

.cyber-button-primary {
    border-radius: 0.5rem;
    background: linear-gradient(to right, #06b6d4, #8b5cf6, #ec4899);
    position: relative;
    clip-path: polygon(0 0, calc(100% - 12px) 0, 100% 100%, 12px 100%);
}

.cyber-button-secondary {
    border-radius: 0.5rem;
    border: 2px solid rgba(6, 182, 212, 0.5);
    background-color: rgba(15, 23, 42, 0.5);
    backdrop-filter: blur(4px);
    color: #22d3ee;
    position: relative;
    clip-path: polygon(0 0, calc(100% - 10px) 0, 100% 100%, 10px 100%);
}

.cyber-button-mega {
    border-radius: 0.75rem;
    background: linear-gradient(to right, #9333ea, #06b6d4, #9333ea);
    background-size: 200% 200%;
    animation: gradient 3s ease infinite;
    position: relative;
    clip-path: polygon(0 0, calc(100% - 15px) 0, 100% 100%, 15px 100%);
    box-shadow: 0 0 30px rgba(139, 92, 246, 0.5), 0 0 60px rgba(34, 211, 238, 0.3);
}

/* Frame Styles */
.cyber-frame {
    border-radius: 1rem;
    border: 2px solid;
    position: relative;
    clip-path: polygon(0 0, calc(100% - 20px) 0, 100% 20px, 100% 100%, 20px 100%, 0 calc(100% - 20px));
}

.cyber-card {
    border-radius: 0.75rem;
    background-color: rgba(30, 41, 59, 0.4);
    backdrop-filter: blur(4px);
    border: 1px solid rgba(51, 65, 85, 0.5);
    position: relative;
    clip-path: polygon(0 0, calc(100% - 15px) 0, 100% 15px, 100% 100%, 15px 100%, 0 calc(100% - 15px));
}

/* Link Styles */
.cyber-link::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 0;
    height: 1px;
    background-color: currentColor;
    transition: all 0.3s ease;
}

.cyber-link:hover::after {
    width: 100%;
}

/* Grid Pattern */
.bg-grid-pattern {
    background-image: 
        linear-gradient(rgba(34, 211, 238, 0.1) 1px, transparent 1px),
        linear-gradient(90deg, rgba(34, 211, 238, 0.1) 1px, transparent 1px);
    background-size: 50px 50px;
    animation: grid-move 20s linear infinite;
}

.cyber-grid {
    background-image: 
        linear-gradient(rgba(139, 92, 246, 0.2) 1px, transparent 1px),
        linear-gradient(90deg, rgba(139, 92, 246, 0.2) 1px, transparent 1px);
    background-size: 30px 30px;
}

/* Glitch Effect */
.glitch {
    position: relative;
    animation: glitch 0.3s ease-in-out;
}

.glitch::before,
.glitch::after {
    content: attr(data-text);
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}

.glitch::before {
    animation: glitch-1 0.3s ease-in-out;
    color: #ff0040;
    z-index: -1;
}

.glitch::after {
    animation: glitch-2 0.3s ease-in-out;
    color: #00ffff;
    z-index: -2;
}

/* Custom Animations */
@keyframes glitch {
    0%, 100% { transform: translate(0); }
    20% { transform: translate(-2px, 2px); }
    40% { transform: translate(-2px, -2px); }
    60% { transform: translate(2px, 2px); }
    80% { transform: translate(2px, -2px); }
}

@keyframes glitch-1 {
    0%, 100% { transform: translate(0); }
    20% { transform: translate(-2px, 2px); }
    40% { transform: translate(-2px, -2px); }
    60% { transform: translate(2px, 2px); }
    80% { transform: translate(2px, -2px); }
}

@keyframes glitch-2 {
    0%, 100% { transform: translate(0); }
    20% { transform: translate(2px, -2px); }
    40% { transform: translate(2px, 2px); }
    60% { transform: translate(-2px, -2px); }
    80% { transform: translate(-2px, 2px); }
}

@keyframes float {
    0%, 100% { transform: translateY(0) rotate(0deg); }
    25% { transform: translateY(-10px) rotate(1deg); }
    50% { transform: translateY(-20px) rotate(0deg); }
    75% { transform: translateY(-10px) rotate(-1deg); }
}

@keyframes pulse-slow {
    0%, 100% { opacity: 0.4; transform: scale(1); }
    50% { opacity: 0.8; transform: scale(1.05); }
}

@keyframes spin-slow {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

@keyframes scan-vertical {
    0% { transform: translateY(-100vh); opacity: 0; }
    10% { opacity: 1; }
    90% { opacity: 1; }
    100% { transform: translateY(100vh); opacity: 0; }
}

@keyframes scan-horizontal {
    0% { transform: translateX(-100vw); opacity: 0; }
    10% { opacity: 1; }
    90% { opacity: 1; }
    100% { transform: translateX(100vw); opacity: 0; }
}

@keyframes scan-qr {
    0% { transform: translateY(-100%); }
    100% { transform: translateY(200%); }
}

@keyframes gradient {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

@keyframes grid-move {
    0% { transform: translate(0, 0); }
    100% { transform: translate(50px, 50px); }
}

/* Animation Classes */
.animate-float {
    animation: float 6s ease-in-out infinite;
}

.animate-pulse-slow {
    animation: pulse-slow 4s ease-in-out infinite;
}

.animate-spin-slow {
    animation: spin-slow 8s linear infinite;
}

.animate-scan-vertical {
    animation: scan-vertical 8s ease-in-out infinite;
}

.animate-scan-horizontal {
    animation: scan-horizontal 12s ease-in-out infinite;
}

.animate-scan-qr {
    animation: scan-qr 2s ease-in-out infinite;
}

.animate-gradient {
    background-size: 200% 200%;
    animation: gradient 3s ease infinite;
}

.bg-size-200 {
    background-size: 200% 200%;
}

/* Shadow Effects */
.shadow-glow-cyan {
    box-shadow: 0 0 20px rgba(34, 211, 238, 0.4), 0 0 40px rgba(34, 211, 238, 0.2);
}

.shadow-glow-purple {
    box-shadow: 0 0 20px rgba(139, 92, 246, 0.4), 0 0 40px rgba(139, 92, 246, 0.2);
}

.shadow-glow-emerald {
    box-shadow: 0 0 20px rgba(16, 185, 129, 0.4), 0 0 40px rgba(16, 185, 129, 0.2);
}

.drop-shadow-glow-cyan {
    filter: drop-shadow(0 0 10px rgba(34, 211, 238, 0.6));
}

.drop-shadow-glow {
    filter: drop-shadow(0 0 8px currentColor);
}

/* Responsive Design */
@media (max-width: 768px) {
    .cyber-button-primary,
    .cyber-button-secondary,
    .cyber-button-mega {
        clip-path: polygon(0 0, calc(100% - 8px) 0, 100% 100%, 8px 100%);
    }
    
    .cyber-frame {
        clip-path: polygon(0 0, calc(100% - 15px) 0, 100% 15px, 100% 100%, 15px 100%, 0 calc(100% - 15px));
    }
    
    .cyber-card {
        clip-path: polygon(0 0, calc(100% - 10px) 0, 100% 10px, 100% 100%, 10px 100%, 0 calc(100% - 10px));
    }
}

/* Dark Mode Enhancements */
@media (prefers-color-scheme: dark) {
    .bg-grid-pattern {
        background-image: 
            linear-gradient(rgba(34, 211, 238, 0.15) 1px, transparent 1px),
            linear-gradient(90deg, rgba(34, 211, 238, 0.15) 1px, transparent 1px);
    }
}

/* Accessibility */
@media (prefers-reduced-motion: reduce) {
    .animate-float,
    .animate-pulse-slow,
    .animate-spin-slow,
    .animate-scan-vertical,
    .animate-scan-horizontal,
    .animate-scan-qr,
    .animate-gradient {
        animation: none;
    }
    
    .glitch,
    .glitch::before,
    .glitch::after {
        animation: none;
    }
}

/* High Contrast Mode */
@media (prefers-contrast: high) {
    .cyber-button,
    .cyber-frame,
    .cyber-card {
        border-width: 2px;
    }
    
    .text-gray-300,
    .text-gray-400 {
        color: #ffffff;
    }
}
</style>