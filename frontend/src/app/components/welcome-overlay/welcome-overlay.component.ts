import { Component, OnDestroy, OnInit } from '@angular/core';

@Component({
  selector: 'app-welcome-overlay',
  imports: [],
  templateUrl: './welcome-overlay.component.html',
  styleUrl: './welcome-overlay.component.css'
})
export class WelcomeOverlayComponent implements OnInit, OnDestroy{
  showWelcomeOverlay = true;
  showOverlay = true;
  private overlayTimeout?: number;
  private readonly OVERLAY_HIDE_DELAY = 5000; // ms


  ngOnInit(): void {
  }

  ngOnDestroy(): void{
    this.clearOverlayTimeout();
  }
  
  // When user start interacting with the view, hide the overlay
  onUserInteractionStart(): void {
    this.showOverlay = false;
    this.clearOverlayTimeout();
  }

  // When user stop interacting with the view, start a timer
  onUserInteractionEnd(): void {
    this.scheduleOverlayShow();
  }

  // After OVERLAY_HIDE_DELAY, show the overlay
  private scheduleOverlayShow(): void {
    this.clearOverlayTimeout();
    this.overlayTimeout = window.setTimeout(() => {
      this.showOverlay = true;
    }, this.OVERLAY_HIDE_DELAY);
  }

  // Clean the timer
  private clearOverlayTimeout(): void {
    if (this.overlayTimeout) {
      clearTimeout(this.overlayTimeout);
      this.overlayTimeout = undefined;
    }
  }
}
