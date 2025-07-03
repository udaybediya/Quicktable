package com.example.quicktable;

import android.content.Intent;
import android.content.res.ColorStateList;
import android.graphics.Color;
import android.os.Bundle;
import android.view.View;
import android.widget.ImageButton;
import android.widget.TextView;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.view.GravityCompat;
import androidx.drawerlayout.widget.DrawerLayout;
import com.google.android.material.navigation.NavigationView;

public class MainActivity extends AppCompatActivity {
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main); // Ensure this matches your layout file

        // Get user data from intent
        Intent intent = getIntent();
        String fullName = intent.hasExtra("full_name") ? intent.getStringExtra("full_name") : "User";
        String details = intent.hasExtra("details") ? intent.getStringExtra("details") : "Unknown";

        // Get references to UI components
        DrawerLayout drawerLayout = findViewById(R.id.main);
        ImageButton navigationButton = findViewById(R.id.NavigationButton);
        NavigationView navigationView = findViewById(R.id.Navigationview);
        navigationView.setItemTextColor(ColorStateList.valueOf(Color.BLACK));
        navigationView.setItemIconTintList(ColorStateList.valueOf(Color.BLACK));

        // Update navigation header with user data
        View headerView = navigationView.getHeaderView(0);
        TextView txtUserName = headerView.findViewById(R.id.textUserName);
        TextView txtUserDetails = headerView.findViewById(R.id.textUserDetails);

        if (fullName != null && details != null) {
            txtUserName.setText(fullName); // Set full_name in the header
            txtUserDetails.setText(details);
        }

        // Open navigation drawer on button click
        navigationButton.setOnClickListener(v -> {
            if (drawerLayout != null) {
                drawerLayout.openDrawer(GravityCompat.START);
            }
        });

        // Handle navigation item clicks
        navigationView.setNavigationItemSelectedListener(item -> {
            int itemId = item.getItemId();

            if (itemId == R.id.menuButton) {
                startActivity(new Intent(this, MenuActivity.class));
            } else if (itemId == R.id.newTableButton) {
                startActivity(new Intent(this, NewtableActivity.class));
            } else if (itemId == R.id.updaterOderButton) {
                startActivity(new Intent(this, UpdateTableActivity.class));
            } else if (itemId == R.id.appearanceButton) {
                // Pass data to AppearanceActivity
                Intent appearanceIntent = new Intent(this, AppearanceActivity.class);
                appearanceIntent.putExtra("full_name", fullName);
                appearanceIntent.putExtra("details", details);
                startActivity(appearanceIntent);
            } else if (itemId == R.id.logoutButton) {
                startActivity(new Intent(this, Logout.class));
            }

            drawerLayout.closeDrawer(GravityCompat.START); // Close the drawer after selection
            return true;
        });

        // Set up button click listeners
        setupButtonClickListeners();
    }

    private void setupButtonClickListeners() {
        // Button to open New Table Activity
        findViewById(R.id.newTableCard).setOnClickListener(v ->
                startActivity(new Intent(this, NewtableActivity.class)));

        // Button to open Edit Table Activity
        findViewById(R.id.updateTableCard).setOnClickListener(v ->
                startActivity(new Intent(this, UpdateTableActivity.class)));

        // Button to open Bill Activity
        findViewById(R.id.billCard).setOnClickListener(v ->
                startActivity(new Intent(this, BillActivity.class)));

        // Button to open Cook Activity
        findViewById(R.id.cookCard).setOnClickListener(v ->
                startActivity(new Intent(this, CookActivity.class)));

        // Button to open Appearance Activity
        findViewById(R.id.appearanceCard).setOnClickListener(v -> {
            // Pass data to AppearanceActivity
            Intent appearanceIntent = new Intent(this, AppearanceActivity.class);
            appearanceIntent.putExtra("full_name", getIntent().getStringExtra("full_name"));
            appearanceIntent.putExtra("details", getIntent().getStringExtra("details"));
            startActivity(appearanceIntent);
        });

        // Button to open Menu Activity
        findViewById(R.id.menuCard).setOnClickListener(v ->
                startActivity(new Intent(this, MenuActivity.class)));
    }
}