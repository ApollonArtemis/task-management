<script setup lang="ts">

import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Textarea } from '@/components/ui/textarea';
import InputError from '@/components/InputError.vue';
import Badge from '@/components/ui/badge/Badge.vue';
import { type BreadcrumbItem } from '@/types';
import { dashboard } from '@/routes';
import { Trash2, CheckCircle2, Circle, Search, X, Plus, Pencil, Loader2, ListTodo, Clock, TrendingUp } from 'lucide-vue-next';
import { ref } from 'vue';
import { watchDebounced } from '@vueuse/core';
import { computed } from 'vue';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});

interface Task {
    nTaskNo: number;
    cTaskName: string;
    cTasksDescription: string;
    cTaskPriority: string;
    cCompleted: boolean;
    created_at: string;
    nTaskListNo: number;
}

interface TaskList {
    nTaskListNo: number;
    cTaskListName: string;
    cTaskListsColor: string;
    task_count: number;
    completed_tasks_count: number;
    created_at: string;
}

const props = defineProps<{
    lists: TaskList[];
    recentTasks: Task[];
    totalTasks: number;
    completedTasks: number;
    pendingTasks: number;
}>();


const completionRate = computed(() =>
props.totalTasks > 0 ? Math.round((props.completedTasks / props.totalTasks) * 100) : 0);
</script>

<template>
    <div class="p-6 space-y-6">
        <div>
            <h1 class="text-3xl font-bold">Dashboard</h1>
            <p class="text-muted-foreground">Overview of your tasks and lists</p>
        </div>

        <div class="grid gap-4 md:grid-cols-4">
            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">Total Lists</CardTitle>
                    <ListTodo class="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">{{ lists.length }}</div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">Total Tasks</CardTitle>
                    <Clock class="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">{{ totalTasks }}</div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">Completed</CardTitle>
                    <CheckCircle2 class="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">{{ completedTasks }}</div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">Completion Rate</CardTitle>
                    <TrendingUp class="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">{{ completionRate }}%</div>
                </CardContent>
            </Card>
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <CardTitle>Your Lists</CardTitle>
                        <Link href="lists">
                            <Button variant="outline" size="sm">View All</Button>
                        </Link>
                    </div>
                </CardHeader>
                <CardContent>
                    <div v-if="lists.length > 0" class="space-y-3">
                        <Link v-for="list in lists"
                            :key="list.nTaskListNo"
                            :href="`/tasks?nTaskListNo=${list.nTaskListNo}`"
                            class="block p-3 rounded-lg border hover:bg-accent transition-colors"
                            >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-3 h-3 rounded-full" :style="{ backgroundColor: list.cTaskListsColor || '#000000' }"></div>
                                    <div>
                                        <p class="font-medium">{{ list.cTaskListName }}</p>
                                        <p class="text-sm text-muted-foreground">{{ list.completed_tasks_count }} / {{ list.task_count }} completed</p>
                                    </div>
                                </div>
                                <span class="text-sm font-medium">{{ list.task_count }}</span>
                            </div>
                        </Link>
                    </div>

                    <div v-else class="text-center py-8 text-muted-foreground">
                        No lists yet
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Recent Tasks</CardTitle>
                </CardHeader>
                <CardContent>
                    <div v-if="recentTasks.length > 0" class="space-y-3">
                        <div v-for="task in recentTasks"
                            :key="task.nTaskNo"
                            class="p-3 rounded-lg border">
                            <div class="flex items-start gap-3">
                                <div class="mt-1 flex-shrink-0 w-4 h-4 rounded-full flex items-center justify-center" :class="task.cCompleted ? 'bg-green-500' : 'border-2'">
                                    <CheckCircle2 v-if="task.cCompleted" class="w-3 h-3 text-white" />
                                </div>
                                <div class="flex-1">
                                    <p class="font-medium" :class="{ 'line-through text-muted-foreground' : task.cCompleted }">{{ task.cTaskName }}</p>
                                    <p class="text-sm text-muted-foreground">{{ task.cTaskPriority }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="text-center py-8 text-muted-foreground">
                        No tasks yet
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>