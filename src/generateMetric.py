import json
import math
import xml.etree.ElementTree as ET

# Loading the thread sizes and pitches
with open('threads.json', 'r') as f:
    threads = json.load(f)

# The thread angles overhang angle = 90 - angle/2
angles = [90, 80, 70, 60, 50]

# The maximum +- tolerance
tolMax = 0.5

# The tolerance step size
tolStep = 0.025

for angle in angles:
    filename = f'../FDM{angle}MetricTrapezoidalThreads.xml'
    name = f'FDM {angle} Degree Metric Trapezoidal Threads'

    # Creating the root XML element
    threadType = ET.Element('ThreadType')
    ET.SubElement(threadType, 'Name').text = name
    ET.SubElement(threadType, 'CustomName').text = name
    ET.SubElement(threadType, 'Unit').text = 'mm'
    ET.SubElement(threadType, 'Angle').text = str(angle)
    ET.SubElement(threadType, 'SortOrder').text = '4'

    for size, pitches in threads.items():
        threadSize = ET.SubElement(threadType, 'ThreadSize')
        ET.SubElement(threadSize, 'Size').text = f'{float(size):.1f}'

        for pitch in pitches:
            designation = ET.SubElement(threadSize, 'Designation')
            designation_text = f'FDM{angle}-{size}x{pitch}'
            ET.SubElement(designation, 'ThreadDesignation').text = designation_text
            ET.SubElement(designation, 'CTD').text = designation_text
            ET.SubElement(designation, 'Pitch').text = f'{float(pitch):.1f}'

            radius = float(size) / 2
            height = math.tan(math.radians(90 - (angle / 2))) * (float(pitch) / 2)

            crestH = math.tan(math.radians(90 - (angle / 2))) * (float(pitch) / 8)
            pitchH = math.tan(math.radians(90 - (angle / 2))) * (float(pitch) / 4)
            rootH = math.tan(math.radians(90 - (angle / 2))) * (float(pitch) / 8)

            MajorRadius = radius
            pitchRadius = radius - pitchH + crestH
            minorRadius = radius - height + rootH + crestH

            tol = 0
            while (round(tol, 6) <= round(tolMax, 6) and
                   round(tol, 6) <= round(crestH, 6) and
                   round(tol, 6) <= round(rootH, 6)):
                externalMajorD = (MajorRadius - tol) * 2
                externalPitchD = (pitchRadius - (tol / math.sin(math.radians(angle / 2)))) * 2
                externalMinorD = (minorRadius - tol) * 2
                internalMajorD = (MajorRadius + tol) * 2
                internalPitchD = (pitchRadius + (tol / math.sin(math.radians(angle / 2)))) * 2
                internalMinorD = (minorRadius + tol) * 2

                thread = ET.SubElement(designation, 'Thread')
                ET.SubElement(thread, 'Gender').text = 'external'
                ET.SubElement(thread, 'Class').text = f'{tol:.3f}e'
                ET.SubElement(thread, 'MajorDia').text = f'{externalMajorD:.6f}'
                ET.SubElement(thread, 'PitchDia').text = f'{externalPitchD:.6f}'
                ET.SubElement(thread, 'MinorDia').text = f'{externalMinorD:.6f}'

                thread = ET.SubElement(designation, 'Thread')
                ET.SubElement(thread, 'Gender').text = 'internal'
                ET.SubElement(thread, 'Class').text = f'{tol:.3f}i'
                ET.SubElement(thread, 'MajorDia').text = f'{internalMajorD:.6f}'
                ET.SubElement(thread, 'PitchDia').text = f'{internalPitchD:.6f}'
                ET.SubElement(thread, 'MinorDia').text = f'{internalMinorD:.6f}'

                tol += tolStep

    # Saving the XML to file
    tree = ET.ElementTree(threadType)
    tree.write(filename, encoding='utf-8', xml_declaration=True)