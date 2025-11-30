<template>
  <n-config-provider>
    <n-modal-provider>
      <n-message-provider>
        <n-dialog-provider>
          <n-notification-provider>
            <router-view />
          </n-notification-provider>
        </n-dialog-provider>
      </n-message-provider>
    </n-modal-provider>
  </n-config-provider>
</template>

<script></script>
