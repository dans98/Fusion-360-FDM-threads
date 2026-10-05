# Fusion-360-FDM-threads

Fusion 360's current selection of thread profiles aren't overly useful for those looking to design parts that will be 3D printed. Standard 60 degree V threads can be printed satisfactorily by decreasing print speed or increasing cooling. However their tolerances are still machining centric and thus can be difficult to print at times.

Thankfully a workaround exists where custom thread profiles can be loaded into Fusion 360. The workaround is explained in this [Autodesk support article](https://knowledge.autodesk.com/support/fusion-360/learn-explore/caas/sfdcarticles/sfdcarticles/Custom-Threads-in-Fusion-360.html). Frustratingly, Fusion will "lose" these thread definitions on each update, but you can use [this plugin to resync them automatically](https://apps.autodesk.com/FUSION/en/Detail/Index?id=1725038115223093226&appLang=en&os=Win64&autostart=true).

Given the above I decided to write a simple script that would generate custom thread profiles that are more conducive to 3D printing. The profiles are trapezoidal in nature with root and crest flats 1/4th the width of the thread pitch. This yields robust threads that will not break easily. The included thread angles are 50, 60, 70, 80, and 90 degrees. For reference the overhang angle of a thread printed in the vertical orientation is 90 - (threadAngle/2) degrees.

Since many are not familiar with thread classes(tolerances) I tried to make the classes self-explanatory. When you select the class drop down you will see 0.###e for external threads, and 0.###i for internal threads. 0.### is the tolerance in millimeters compared to the nominal thread form. External threads are smaller than the nominal, and internal threads are larger than the nominal. If you designed a bolt with a class of 0.100e and a nut with a class of 0.100i, they would have a 0.1 + 0.1 = 0.2mm tolerance/gap between them when threaded together.

## Use the thread profiles in Fusion 360

The XML files in the repository root define five thread families: 50, 60, 70, 80, and 90 degree metric trapezoidal threads.

1. Close Fusion 360.
2. Copy the XML file or files you want to use into Fusion's `ThreadData` folder. On Windows, the folder is usually under:

   ```text
   %localappdata%\Autodesk\webdeploy\Production\<version ID>\Fusion\Server\Fusion\Configuration\ThreadData
   ```

   Replace `<version ID>` with the folder for the current Fusion installation. If the folder is hard to locate or Fusion has moved it, see [Autodesk's instructions for custom threads that do not appear](https://www.autodesk.com/support/technical/article/caas/sfdcarticles/sfdcarticles/Custom-threads-not-showing-in-Fusion.html).
3. Reopen Fusion. In a design, select **Create > Thread**, choose a cylindrical face, then select the desired FDM thread type, size, designation, and class. Enable **Modeled** if you need actual thread geometry rather than a cosmetic thread.

Fusion updates may install a new version folder, so the XML files may need to be copied into the new `ThreadData` folder again. The existing [ThreadKeeper add-in](https://apps.autodesk.com/FUSION/en/Detail/Index?id=1725038115223093226&appLang=en&os=Win64&autostart=true) can automate keeping custom thread files available across updates.

If a profile does not appear, confirm the XML is in the `ThreadData` folder for the latest Fusion version and that its `<Name>` and `<CustomName>` values are unique among the custom thread files. See Autodesk's [troubleshooting guide](https://www.autodesk.com/support/technical/article/caas/sfdcarticles/sfdcarticles/Custom-threads-not-showing-in-Fusion.html).

### Choosing a profile and fit

| File | Thread angle |
| --- | ---: |
| `FDM50MetricTrapezoidalThreads.xml` | 50° |
| `FDM60MetricTrapezoidalThreads.xml` | 60° |
| `FDM70MetricTrapezoidalThreads.xml` | 70° |
| `FDM80MetricTrapezoidalThreads.xml` | 80° |
| `FDM90MetricTrapezoidalThreads.xml` | 90° |

Each family contains the nominal sizes and pitches listed in [`src/threads.json`](src/threads.json). In Fusion, classes ending in `e` are for external threads and classes ending in `i` are for internal threads. The number is a radial offset in millimeters from the nominal profile. For example, pairing `0.100e` with `0.100i` gives about 0.2 mm of diametral clearance at the major and minor diameters. Actual printed fit depends on the printer, material, and process, so choose a clearance that suits your setup.

## Regenerate the XML files

The Python generator reads the diameter and pitch list from `src/threads.json` and rewrites all five XML files in the repository root. Run it from the `src` directory because it reads `threads.json` relative to the current directory:

```sh
cd src
python3 generateMetric.py
```

Python 3 is required; the generator uses only Python's standard library. To add or remove available sizes and pitches, edit `threads.json` first, then run the generator. The alternative `generateMetric.php` script can also regenerate the files when run from `src` with PHP CLI and its SimpleXML and DOM extensions installed:

```sh
cd src
php generateMetric.php
```
