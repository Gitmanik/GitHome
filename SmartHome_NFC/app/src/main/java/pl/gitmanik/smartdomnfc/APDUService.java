package pl.gitmanik.smartdomnfc;

import android.content.SharedPreferences;
import android.nfc.cardemulation.HostApduService;
import android.os.Bundle;
import android.preference.PreferenceManager;
import android.util.Log;

import androidx.core.app.NotificationManagerCompat;

import java.nio.charset.StandardCharsets;
import java.util.Random;

public class APDUService extends HostApduService {
    @Override
    public byte[] processCommandApdu(byte[] apdu, Bundle extras) {
        SharedPreferences prefs = PreferenceManager.getDefaultSharedPreferences(this);
        if (selectAidApdu(apdu)) {
            if (!prefs.contains("appID"))
                prefs.edit().putString("appID", new Random().ints(97, 122+ 1)
                        .limit(16)
                        .collect(StringBuilder::new, StringBuilder::appendCodePoint, StringBuilder::append)
                        .toString()).apply();

            Log.i("SmartdomKlucz", "Application selected. UUID: " + prefs.getString("appID", null));

            return prefs.getString("appID", null).getBytes(StandardCharsets.UTF_8);
        }
        else {
            Log.i("SmartdomKlucz", "Received: " + new String(apdu));
            return "Hello2!".getBytes();
        }
    }

    @Override
    public void onDeactivated(int i) {
        Log.i("SmartdomKlucz", "Deactivated: " + i);
    }

    private boolean selectAidApdu(byte[] apdu) {
        return apdu.length >= 2 && apdu[0] == (byte)0 && apdu[1] == (byte)0xa4;
    }
}
