<!-- ✅ Floating Chatbot UI with Collapse Button -->
<style>
#chatbot-toggle {
  position: fixed;
  bottom: 20px;
  right: 20px;
  background: #003580;
  color: white;
  border: none;
  border-radius: 50%;
  width: 50px;
  height: 50px;
  font-size: 24px;
  cursor: pointer;
  z-index: 1001;
  box-shadow: 0 4px 12px rgba(0,0,0,0.3);
}

#chatbot {
  position: fixed;
  bottom: 80px;
  right: 20px;
  width: 300px;
  height: 420px;
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.1);
  display: none;
  flex-direction: column;
  overflow: hidden;
  font-family: 'Segoe UI', sans-serif;
  z-index: 1000;
}

#chatbot-header {
  background: #003580;
  color: white;
  padding: 12px;
  font-weight: bold;
  text-align: center;
}

#chat-messages {
  flex: 1;
  padding: 10px;
  overflow-y: auto;
  font-size: 14px;
}

.message {
  margin-bottom: 10px;
}

.user { text-align: right; color: #003580; }
.bot  { text-align: left; color: #333; }

#chat-form {
  display: flex;
  border-top: 1px solid #ddd;
}

#chat-input {
  flex: 1;
  padding: 8px;
  border: none;
  outline: none;
}

#send-btn {
  background: #febb02;
  border: none;
  padding: 0 16px;
  cursor: pointer;
}
</style>

<!-- Toggle Button -->
<button id="chatbot-toggle">💬</button>

<!-- Chatbot Box -->
<div id="chatbot">
  <div id="chatbot-header">Ask JOJO 🤖</div>
  <div id="chat-messages"></div>
  <form id="chat-form" onsubmit="sendMessage(event)">
    <input type="text" id="chat-input" placeholder="Ask me anything..." required />
    <button id="send-btn">➤</button>
  </form>
</div>

<script>
const toggleBtn = document.getElementById('chatbot-toggle');
const chatbot = document.getElementById('chatbot');
const messages = document.getElementById('chat-messages');
const form = document.getElementById('chat-form');
const input = document.getElementById('chat-input');

// Toggle chatbot visibility
toggleBtn.onclick = () => {
  chatbot.style.display = chatbot.style.display === 'none' ? 'flex' : 'none';
};

function sendMessage(e) {
  e.preventDefault();
  const userMsg = input.value.trim();
  if (!userMsg) return;

  appendMessage('user', userMsg);
  input.value = '';

  // Simulated bot response
  setTimeout(() => {
    const response = getBotResponse(userMsg.toLowerCase());
    appendMessage('bot', response);
  }, 500);
}

function appendMessage(sender, text) {
  const msg = document.createElement('div');
  msg.className = 'message ' + sender;
  msg.textContent = text;
  messages.appendChild(msg);
  messages.scrollTop = messages.scrollHeight;
}

function getBotResponse(message) {
  if (message.includes("wifi")) return "Yes, we offer free high-speed Wi-Fi 📶";
  if (message.includes("pickup")) return `Sure! Here's how our hotel pickup works:

1️⃣ After you book a room, you’ll be asked if you want a hotel pickup.  
2️⃣ If you choose yes, you’ll enter your pickup location, time, phone number, and car type.  
3️⃣ Our system calculates the distance and cost automatically using Google Maps.  
4️⃣ Once confirmed, a hotel driver is assigned and notified via SMS.  
5️⃣ The driver will contact you before pickup time.  
6️⃣ Just be ready at your selected location — and enjoy the ride! 🚗✨`;

  if (message.includes("room")) return "Our rooms start at GHS 250 per night 🛏️";
  if (message.includes("food") || message.includes("breakfast")) return "Breakfast is included with your room 🥐";
  if (message.includes("book")) return "You can click 'Book Now' on the room you like!";
  return "I'm here to help! Ask me about rooms, pickup, Wi-Fi, or booking.";
}
</script>
