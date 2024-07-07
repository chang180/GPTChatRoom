<template>
    <div>
        <h1>Chat Room</h1>
        <div id="messages">
            <div v-for="message in messages" :key="message.id">
                <strong>{{ message.user }}:</strong> {{ message.text }}
            </div>
        </div>
        <input
            v-model="newMessage"
            @keyup.enter="sendMessage"
            placeholder="Type a message"
        />
        <button @click="sendMessage">Send</button>
    </div>
</template>

<script>
import { ref } from "vue";
import { usePage } from "@inertiajs/inertia-vue3";

export default {
    setup() {
        const { props } = usePage();
        const messages = ref([]);
        const newMessage = ref("");

        const sendMessage = async () => {
            if (newMessage.value.trim() !== "") {
                messages.value.push({
                    user: props.auth.user.name,
                    text: newMessage.value,
                });
                const response = await axios.post(route("send-message"), {
                    message: newMessage.value,
                });
                messages.value.push({
                    user: "GPT-3.5",
                    text: response.data.choices[0].message.content,
                });
                newMessage.value = "";
            }
        };

        return {
            messages,
            newMessage,
            sendMessage,
        };
    },
};
</script>

<style scoped>
#messages {
    border: 1px solid #ccc;
    padding: 10px;
    height: 300px;
    overflow-y: scroll;
}
input {
    width: calc(100% - 100px);
}
button {
    width: 80px;
}
</style>
