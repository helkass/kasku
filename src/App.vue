<template>
  <n-config-provider>
    <n-loading-bar-provider>
      <n-modal-provider>
        <n-message-provider>
          <n-dialog-provider>
            <n-notification-provider>
              <router-view />
            </n-notification-provider>
          </n-dialog-provider>
        </n-message-provider>
      </n-modal-provider>
    </n-loading-bar-provider>
  </n-config-provider>
</template>

<script></script>
